<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CalendarModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Date;
use Contao\ModuleCalendar;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Doctrine\DBAL\Connection;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use InspiredMinds\ContaoEventRegistration\EventsModuleProxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsFrontendModule(self::TYPE, 'events', priority: 1)]
class EventRegistrationCalendarController extends AbstractFrontendModuleController
{
    public const TYPE = 'event_registration_calendar';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly TokenChecker $tokenChecker,
        private readonly Connection $db,
        private readonly TranslatorInterface $translator,
        private readonly EventsModuleProxy $eventsModuleProxy,
        private readonly SymfonyResponseTagger|null $responseTagger,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $calendarIds = $this->sortOutProtected(StringUtil::deserialize($model->cal_calendar, true));

        if (!$calendarIds) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        $url = strtok($request->getUri(), '?');

        if ($this->responseTagger) {
            $this->responseTagger->addTags(array_map(static fn (int $id): string => 'contao.db.tl_calendar.'.$id, $calendarIds));
        }

        $monthParam = $request->query->get('month');
        $dayParam = $request->query->get('day');

        // Create the date object
        try {
            if ($monthParam) {
                $date = new Date($monthParam, 'Ym');
            } elseif ($dayParam) {
                $date = new Date($dayParam, 'Ymd');
            } else {
                $date = new Date();
            }
        } catch (\OutOfBoundsException $e) {
            throw new PageNotFoundException(previous: $e);
        }

        $time = Date::floorToMinute();

        // Find the boundaries
        $showUnpublished = $this->tokenChecker->isPreviewMode();
        $minmax = $this->db->fetchAssociative('SELECT MIN(startTime) AS dateFrom, MAX(endTime) AS dateTo, MAX(repeatEnd) AS repeatUntil FROM tl_calendar_events WHERE pid IN('.implode(',', array_map('\intval', $calendarIds)).')'.(!$showUnpublished ? " AND published='1' AND (start='' OR start<=$time) AND (stop='' OR stop>$time)" : ''));
        $dateFrom = $minmax['dateFrom'];
        $dateTo = $minmax['dateTo'];
        $repeatUntil = $minmax['repeatUntil'];

        if (isset($GLOBALS['TL_HOOKS']['findCalendarBoundaries']) && \is_array($GLOBALS['TL_HOOKS']['findCalendarBoundaries'])) {
            foreach ($GLOBALS['TL_HOOKS']['findCalendarBoundaries'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($dateFrom, $dateTo, $repeatUntil, new ModuleCalendar($model));
            }
        }

        $firstMonth = date('Ym', min($dateFrom, $time));
        $lastMonth = date('Ym', max($dateTo, $repeatUntil, $time));

        // The given month is out of scope
        if ($monthParam && ($monthParam < $firstMonth || $monthParam > $lastMonth)) {
            throw new PageNotFoundException();
        }

        // The given day is out of scope
        if ($dayParam && ($dayParam < date('Ymd', min($dateFrom, $time)) || $dayParam > date('Ymd', max($dateTo, $repeatUntil, $time)))) {
            throw new PageNotFoundException();
        }

        // Store year and month
        $year = (int) date('Y', $date->tstamp);
        $month = (int) date('m', $date->tstamp);

        $template->year = $year;
        $template->month = $month;

        // Previous month
        $prevMonth = 1 === $month ? 12 : $month - 1;
        $prevYear = 1 === $month ? $year - 1 : $year;
        $lblPrevious = $this->translator->trans('MONTHS.'.($prevMonth - 1), [], 'contao_default').' '.$prevYear;
        $intPrevYm = (int) ($prevYear.str_pad((string) $prevMonth, 2, '0', STR_PAD_LEFT));

        // Only generate a link if there are events (see #4160)
        if ($intPrevYm >= $firstMonth) {
            $template->prevHref = $url.'?month='.$intPrevYm;
            $template->prevTitle = StringUtil::specialchars($lblPrevious);
            $template->prevLink = $this->translator->trans('MSC.cal_previous', [], 'contao_default').' '.$lblPrevious;
            $template->prevLabel = $this->translator->trans('MSC.cal_previous', [], 'contao_default');
        }

        // Current month
        $template->current = $GLOBALS['TL_LANG']['MONTHS'][date('m', $date->tstamp) - 1].' '.date('Y', $date->tstamp);

        // Next month
        $nextMonth = 12 === $month ? 1 : $month + 1;
        $nextYear = 12 === $month ? $year + 1 : $year;
        $lblNext = $this->translator->trans('MONTHS.'.($nextMonth - 1), [], 'contao_default').' '.$nextYear;
        $intNextYm = $nextYear.str_pad((string) $nextMonth, 2, '0', STR_PAD_LEFT);

        // Only generate a link if there are events (see #4160)
        if ($intNextYm <= $lastMonth) {
            $template->nextHref = $url.'?month='.$intNextYm;
            $template->nextTitle = StringUtil::specialchars($lblNext);
            $template->nextLink = $lblNext.' '.$this->translator->trans('MSC.cal_next', [], 'contao_default');
            $template->nextLabel = $this->translator->trans('MSC.cal_next', [], 'contao_default');
        }

        $template->days = $this->compileDays($model);
        $template->weeks = $this->compileWeeks($model, $date, $request, $calendarIds, $url);
        $template->substr = $this->translator->trans('MSC.dayShortLength', [], 'contao_default');

        return $template->getResponse();
    }

    private function sortOutProtected(array $calendarIds): array
    {
        if ([] === $calendarIds) {
            return $calendarIds;
        }

        $calendars = CalendarModel::findMultipleByIds($calendarIds);
        $calendarIds = [];

        foreach ($calendars ?? [] as $calendar) {
            if ($calendar->protected && !$this->authChecker->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, StringUtil::deserialize($calendar->groups, true))) {
                continue;
            }

            $calendarIds[] = $calendar->id;
        }

        return array_map('intval', $calendarIds);
    }

    /**
     * Return the week days and labels as array.
     */
    private function compileDays(ModuleModel $model): array
    {
        $days = [];

        for ($i = 0; $i < 7; ++$i) {
            $class = '';
            $currentDay = ($i + $model->cal_startDay) % 7;

            if (0 === $i) {
                $class .= ' col_first';
            } elseif (6 === $i) {
                $class .= ' col_last';
            }

            if (0 === $currentDay || 6 === $currentDay) {
                $class .= ' weekend';
            }

            $days[$currentDay] = ['class' => $class, 'name' => $GLOBALS['TL_LANG']['DAYS'][$currentDay]];
        }

        return $days;
    }

    /**
     * Return all weeks of the current month as array.
     */
    private function compileWeeks(ModuleModel $model, Date $date, Request $request, array $calendarIds, string $url): array
    {
        $daysInMonth = date('t', $date->monthBegin);
        $firstDayOffset = date('w', $date->monthBegin) - $model->cal_startDay;

        if ($firstDayOffset < 0) {
            $firstDayOffset += 7;
        }

        // Handle featured events
        $featured = null;

        if ('featured' === $model->cal_featured) {
            $featured = true;
        } elseif ('unfeatured' === $model->cal_featured) {
            $featured = false;
        }

        $columnCount = -1;
        $numberOfRows = ceil(($daysInMonth + $firstDayOffset) / 7);
        $allEvents = $this->eventsModuleProxy->collectAllEvents($calendarIds, (int) $date->monthBegin, (int) $date->monthEnd, $featured);
        $days = [];

        // Compile days
        for ($i = 1; $i <= $numberOfRows * 7; ++$i) {
            $week = floor(++$columnCount / 7);
            $day = $i - $firstDayOffset;
            $currentDay = ($i + $model->cal_startDay) % 7;

            $strWeekClass = 'week_'.$week;
            $strWeekClass .= 0 === $week ? ' first' : '';
            $strWeekClass .= $week === $numberOfRows - 1 ? ' last' : '';

            $strClass = $currentDay < 2 ? ' weekend' : '';
            $strClass .= 1 === $i || 8 === $i || 15 === $i || 22 === $i || 29 === $i || 36 === $i ? ' col_first' : '';
            $strClass .= 7 === $i || 14 === $i || 21 === $i || 28 === $i || 35 === $i || 42 === $i ? ' col_last' : '';

            // Add timestamp to all cells
            $days[$strWeekClass][$i]['timestamp'] = strtotime(($day - 1).' day', $date->monthBegin);

            // Empty cell
            if ($day < 1 || $day > $daysInMonth) {
                $days[$strWeekClass][$i]['label'] = '&nbsp;';
                $days[$strWeekClass][$i]['class'] = 'days empty'.$strClass;
                $days[$strWeekClass][$i]['events'] = [];

                continue;
            }

            $key = date('Ym', $date->tstamp).(\strlen((string) $day) < 2 ? '0'.$day : $day);
            $strClass .= $key === date('Ymd') ? ' today' : '';

            // Mark the selected day (see #1784)
            if ($key === $request->query->get('day')) {
                $strClass .= ' selected';
            }

            // Inactive days
            if ('' === $key || '0' === $key || !isset($allEvents[$key])) {
                $days[$strWeekClass][$i]['label'] = $day;
                $days[$strWeekClass][$i]['class'] = 'days'.$strClass;
                $days[$strWeekClass][$i]['events'] = [];

                continue;
            }

            $events = [];

            // Get all events of a day
            foreach ($allEvents[$key] as $v) {
                foreach ($v as $vv) {
                    $events[] = $vv;
                }
            }

            $days[$strWeekClass][$i]['label'] = $day;
            $days[$strWeekClass][$i]['class'] = 'days active'.$strClass;
            $days[$strWeekClass][$i]['href'] = $url.'?day='.$key;
            $days[$strWeekClass][$i]['title'] = \sprintf(StringUtil::specialchars($this->translator->trans('MSC.cal_events', [], 'contao_default')), \count($events));
            $days[$strWeekClass][$i]['events'] = $events;
        }

        return $days;
    }
}

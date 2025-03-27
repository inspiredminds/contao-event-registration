<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\ModuleCalendar;
use Contao\ModuleModel;
use Contao\PageModel;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use Nyholm\Psr7\Uri;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(self::TYPE, 'events', priority: 1)]
class EventRegistrationCalendarController extends ModuleCalendar
{
    public const TYPE = 'event_registration_calendar';

    protected $strTemplate = 'mod_'.self::TYPE;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EventRegistration $eventRegistration,
    ) {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function compile(): void
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return;
        }

        // Redirect to target
        if (null !== $request->query->get('continue') && ($target = PageModel::findById($this->jumpTo))) {
            $url = $target->getAbsoluteUrl();

            if ($eventIds = $request->query->all('event')) {
                $url .= '?'.http_build_query(['event' => $eventIds]);
            }

            throw new RedirectResponseException($url);
        }

        // Redirect to prev/next
        if ($prevHref = $request->query->get('prev')) {
            throw new RedirectResponseException($this->processRedirectUrl($request, $prevHref));
        }

        if ($nextHref = $request->query->get('next')) {
            throw new RedirectResponseException($this->processRedirectUrl($request, $nextHref));
        }

        // Override the URL as we are using jumpTo for the form's action
        $this->strUrl = $request->getBaseUrl().$request->getPathInfo();

        // Override cal_template
        $this->cal_ctemplate = $this->cal_ctemplate_er ?: 'cal_event_registration';

        // Store the current selection
        $this->Template->selectedIds = array_map('intval', $request->query->get('event') ?? []);

        parent::compile();

        // Set form action
        $this->Template->action = $request->getBaseUrl().$request->getPathInfo();
    }

    /**
     * @param list<int> $calendarIds
     * @param int       $start
     * @param int       $end
     * @param bool|null $featured
     */
    protected function getAllEvents($calendarIds, $start, $end, $featured = null): array
    {
        $allEvents = parent::getAllEvents($calendarIds, $start, $end, $featured);
        $eventIds = [];

        // Extract event IDs
        foreach ($allEvents as $a => $aa) {
            foreach ($aa as $b => $bb) {
                foreach ($bb as $c => $event) {
                    $eventIds[] = (int) $event['id'];
                    $allEvents[$a][$b][$c]['canRegister'] = $this->eventRegistration->canRegister(CalendarEventsModel::findById($event['id']));
                    $allEvents[$a][$b][$c]['isSelected'] = \in_array((int) $event['id'], $this->Template->selectedIds ?? [], true);
                }
            }
        }

        // Remove currently shown events from current selection in template
        $this->Template->selectedIds = array_diff($this->Template->selectedIds ?? [], $eventIds);

        return $allEvents;
    }

    private function processRedirectUrl(Request $request, string $redirectUrl): string
    {
        $baseUrl = $request->getBaseUrl().$request->getPathInfo();

        // Validate redirect URL against the current base URL
        \assert(strtok($redirectUrl, '?') === $baseUrl);

        $redirectUri = new Uri($redirectUrl);
        parse_str($redirectUri->getQuery(), $params);

        if ($events = $request->query->all('event')) {
            $params['event'] = $events;
        }

        return (string) $redirectUri->withQuery(http_build_query($params));
    }
}

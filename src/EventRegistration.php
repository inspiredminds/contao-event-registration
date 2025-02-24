<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Controller;
use Contao\Date;
use Contao\Input;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Terminal42\ChangeLanguage\Terminal42ChangeLanguageBundle;

class EventRegistration
{
    public static $eventTokens = [
        'title',
        'location',
        'alias',
        'startTime',
        'endTime',
        'startDate',
        'endDate',
    ];

    public function __construct(
        private readonly Connection $db,
        private array $bundles,
    ) {
    }

    /**
     * Adds some functions to the template. Also adds the data from the main record.
     */
    public function addTemplateData(Template $template, CalendarEventsModel $event): void
    {
        $template->canRegister = fn (): bool => $this->canRegister($event);

        $template->registrationForm = fn (): string => $this->getRegistrationForm($event);

        $template->isFull = fn (): bool => $this->isFull($event);

        $template->registrationCount = fn (): int => $this->getRegistrationCount($event);

        $template->isWaitingList = fn (): bool => $this->isWaitingList($event);

        $mainEvent = $this->getMainEvent($event);

        $template->reg_min = $mainEvent->reg_min;
        $template->reg_max = $mainEvent->reg_max;
        $template->reg_regEnd = $mainEvent->reg_regEnd;
        $template->reg_cancelEnd = $mainEvent->reg_cancelEnd;
    }

    public function canRegister(CalendarEventsModel $event): bool
    {
        if (!$event->reg_enable) {
            return false;
        }

        if (empty($event->reg_form)) {
            return false;
        }

        $event = $this->getMainEvent($event);

        if (!empty($event->reg_regEnd) && time() > $event->reg_regEnd) {
            return false;
        }

        return $this->isWaitingList($event) || !$this->isFull($event);
    }

    public function isWaitingList(CalendarEventsModel $event): bool
    {
        $event = $this->getMainEvent($event);

        return '' !== (string) $event->reg_max && $event->reg_enableWaitingList && $this->isFull($event);
    }

    public function isFull(CalendarEventsModel $event): bool
    {
        $event = $this->getMainEvent($event);

        // No limit defined?
        if ('' === (string) $event->reg_max) {
            return false;
        }

        $count = $this->getRegistrationCount($event, true);

        return $count >= (int) $event->reg_max;
    }

    public function getRegistrationCount(CalendarEventsModel $event, bool $excludeWaitingList = false): int
    {
        $event = $this->getMainEvent($event);

        $query = 'SELECT SUM(amount) FROM tl_event_registration WHERE pid = ? AND cancelled != 1';

        if ($event->reg_requireConfirm) {
            $query .= ' AND confirmed = 1';
        }

        if ('' !== (string) $event->reg_max && $event->reg_enableWaitingList && $excludeWaitingList) {
            $query .= ' AND waiting != 1';
        }

        $query .= ';';

        return (int) $this->db->fetchOne($query, [(int) $event->id]);
    }

    public function getRegistrationForm(CalendarEventsModel $event): string
    {
        if (!$this->canRegister($event)) {
            return '';
        }

        return Controller::getForm($event->reg_form);
    }

    /**
     * Checks whether this form is an event registration form.
     *
     * @param Form|FormModel $form
     */
    public function isEventRegistrationForm($form): bool
    {
        return null !== CalendarEventsModel::findBy(["reg_enable = '1'", 'reg_form = ?'], [$form->id]);
    }

    /**
     * Returns the current event based on the auto_item.
     */
    public function getCurrentEvent(): CalendarEventsModel|null
    {
        $item = Input::get('auto_item');

        if (empty($item)) {
            return null;
        }

        return CalendarEventsModel::findByIdOrAlias($item);
    }

    /**
     * Creates a simple token array with the given even data and optionally event registration data.
     */
    public function getSimpleTokens(CalendarEventsModel $event, EventRegistrationModel|null $registration = null): array
    {
        $tokens = [];

        Controller::loadDataContainer('tl_calendar_events');
        $dcaFields = &$GLOBALS['TL_DCA']['tl_calendar_events']['fields'];

        foreach ($dcaFields as $name => $config) {
            $value = $event->{$name};

            if ($value && isset($config['eval']['rgxp']) && \in_array($config['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                $value = (new Date($value))->{$config['eval']['rgxp']};
            }

            $tokens['event_'.$name] = $value;
        }

        if ($registration) {
            Controller::loadDataContainer('tl_event_registration');
            $dcaFields = &$GLOBALS['TL_DCA']['tl_event_registration']['fields'];
            $data = $registration->getCombinedRow();

            foreach ($data as $key => $value) {
                if (isset($dcaFields[$key])) {
                    $config = &$dcaFields[$key];

                    if ($value && isset($config['eval']['rgxp']) && \in_array($config['eval']['rgxp'], ['date', 'time', 'datim'], true)) {
                        $value = (new Date($value))->{$config['eval']['rgxp']};
                    }
                }

                $tokens['reg_'.$key] = \is_array($value) ? implode(', ', $value) : $value;
            }

            $tokens['reg_confirm_url'] = $this->createStatusUpdateUrl($event, $registration, EventRegistrationConfirmController::ACTION);
            $tokens['reg_cancel_url'] = $this->createStatusUpdateUrl($event, $registration, EventRegistrationCancelController::ACTION);
        }

        $tokens['reg_count'] = $this->getRegistrationCount($event);

        return $tokens;
    }

    /**
     * Returns the main event connected via changelanguage, if applicable.
     */
    public function getMainEvent(CalendarEventsModel $event): CalendarEventsModel
    {
        // No main event defined
        if (empty($event->languageMain)) {
            return $event;
        }

        // If changelanguage is not installed, no main event exists
        if (!isset($this->bundles['changelanguage']) && !\in_array(Terminal42ChangeLanguageBundle::class, $this->bundles, true)) {
            return $event;
        }

        $calendar = CalendarModel::findById((int) $event->pid);

        // If the calendar is not configured properly, no main event exists
        if (null === $calendar || empty($calendar->master)) {
            return $event;
        }

        $mainEvent = CalendarEventsModel::findById((int) $event->languageMain);

        if (null !== $mainEvent) {
            return $mainEvent;
        }

        return $event;
    }

    /**
     * Creates the URL for updating the status to confirmed or cancelled.
     */
    public function createStatusUpdateUrl(CalendarEventsModel $event, EventRegistrationModel $registration, string $action): string
    {
        if (!\in_array($action, [EventRegistrationConfirmController::ACTION, EventRegistrationCancelController::ACTION], true)) {
            throw new \InvalidArgumentException('Invalid action parameter "'.$action.'."');
        }

        $params = [
            'action' => $action,
            'uuid' => $registration->uuid,
        ];

        $calendar = CalendarModel::findById($event->pid);

        $page = null;

        switch ($action) {
            case EventRegistrationConfirmController::ACTION:
                if ($calendar->reg_confirm_page) {
                    $page = PageModel::findById($calendar->reg_confirm_page);
                }

                break;

            case EventRegistrationCancelController::ACTION:
                if ($calendar->reg_cancel_page) {
                    $page = PageModel::findById($calendar->reg_cancel_page);
                }

                break;
        }

        if (null === $page) {
            $page = PageModel::findById($calendar->jumpTo);

            return $page->getAbsoluteUrl('/'.$event->alias).'?'.http_build_query($params);
        }

        return $page->getAbsoluteUrl().'?'.http_build_query($params);
    }

    /**
     * @param Collection|list<EventRegistrationModel> $registrations
     */
    public function createStatusUpdateUrlMultiple(Collection|array $registrations, string $action): string
    {
        if (!\in_array($action, [EventRegistrationConfirmController::ACTION, EventRegistrationCancelController::ACTION], true)) {
            throw new \InvalidArgumentException('Invalid action parameter "'.$action.'."');
        }

        if ($registrations instanceof Collection) {
            $registrations = $registrations->getModels();
        }

        /** @var list<EventRegistrationModel> $registrations */
        if ([] === $registrations) {
            throw new \RuntimeException('No registrations given.');
        }

        $params = [
            'action' => $action,
            'uuid' => array_map(static fn (EventRegistrationModel $reg): string => $reg->uuid, $registrations),
        ];

        if (!$firstEvent = CalendarEventsModel::findById(reset($registrations)->pid)) {
            throw new \RuntimeException('Could not load event.');
        }

        $calendar = CalendarModel::findById($firstEvent->pid);

        $page = null;

        switch ($action) {
            case EventRegistrationConfirmController::ACTION:
                if ($calendar->reg_confirm_page) {
                    $page = PageModel::findById($calendar->reg_confirm_page);
                }

                break;

            case EventRegistrationCancelController::ACTION:
                if ($calendar->reg_cancel_page) {
                    $page = PageModel::findById($calendar->reg_cancel_page);
                }

                break;
        }

        if (null === $page) {
            $page = PageModel::findById($calendar->jumpTo);

            return $page->getAbsoluteUrl('/'.$firstEvent->alias).'?'.http_build_query($params);
        }

        return $page->getAbsoluteUrl().'?'.http_build_query($params);
    }
}

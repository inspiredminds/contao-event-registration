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
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationCancelController;
use InspiredMinds\ContaoEventRegistration\Controller\FrontendModule\EventRegistrationConfirmController;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

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

    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Adds some functions to the template. Also adds the data from the main record.
     */
    public function addTemplateData(Template $template, CalendarEventsModel $event): void
    {
        $template->canRegister = function () use ($event): bool {
            return $this->canRegister($event);
        };

        $template->registrationForm = function () use ($event): string {
            return $this->getRegistrationForm($event);
        };

        $template->isFull = function () use ($event): bool {
            return $this->isFull($event);
        };

        $template->registrationCount = function () use ($event): int {
            return $this->getRegistrationCount($event);
        };

        if (!empty($event->languageMain)) {
            $event = $this->getMainEvent($event);
            $template->reg_min = $event->reg_min;
            $template->reg_max = $event->reg_max;
            $template->reg_regEnd = $event->reg_regEnd;
            $template->reg_cancelEnd = $event->reg_cancelEnd;
        }
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

        return !$this->isFull($event);
    }

    public function isFull(CalendarEventsModel $event): bool
    {
        $event = $this->getMainEvent($event);

        // No limit defined?
        if (null === $event->reg_max || '' === $event->reg_max) {
            return false;
        }

        $count = $this->getRegistrationCount($event);

        return $count >= (int) $event->reg_max;
    }

    public function getRegistrationCount(CalendarEventsModel $event): int
    {
        $event = $this->getMainEvent($event);

        $query = 'SELECT SUM(amount) FROM tl_event_registration WHERE pid = ? AND cancelled != 1';

        if ($event->reg_requireConfirm) {
            $query .= ' AND confirmed = 1';
        }

        $query .= ';';

        $sum = $this->db
            ->executeQuery($query, [(int) $event->id])
            ->fetchColumn()
        ;

        if (false === $sum) {
            return 0;
        }

        return (int) $sum;
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
    public function getCurrentEvent(): ?CalendarEventsModel
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
    public function getSimpleTokens(CalendarEventsModel $event, ?EventRegistrationModel $registration = null): array
    {
        $event = $this->getMainEvent($event);

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

        if (null !== $registration) {
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

                $tokens['reg_'.$key] = $value;
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
        if (empty($event->languageMain)) {
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
                    $page = PageModel::findByPk($calendar->reg_confirm_page);
                }

                break;

            case EventRegistrationCancelController::ACTION:
                if ($calendar->reg_cancel_page) {
                    $page = PageModel::findByPk($calendar->reg_cancel_page);
                }

                break;
        }

        if (null === $page) {
            $page = PageModel::findById($calendar->jumpTo);

            return $page->getAbsoluteUrl('/'.$event->alias).'?'.http_build_query($params);
        }

        return $page->getAbsoluteUrl().'?'.http_build_query($params);
    }
}

<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\FormField;

use Contao\CalendarEventsModel;
use Contao\System;
use Contao\Widget;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\EventsModuleProxy;
use Symfony\Component\HttpFoundation\RequestStack;

class EventRegistrationFormField extends Widget
{
    protected $blnSubmitInput = true;

    protected $blnForAttribute = true;

    protected $strTemplate = 'form_event_registration';

    protected $strPrefix = 'widget widget-event-registration';

    public function generate(): string
    {
        // Not actually used
        return '';
    }

    public function parse($attributes = null): string
    {
        $container = System::getContainer();
        /** @var EventRegistration $eventRegistration */
        $eventRegistration = $container->get(EventRegistration::class);
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');
        /** @var EventsModuleProxy $eventsModuleProxy */
        $eventsModuleProxy = $container->get(EventsModuleProxy::class);
        $events = [];

        foreach ($requestStack->getCurrentRequest()?->query->all('event') ?? [] as $eventId) {
            if (!$event = CalendarEventsModel::findById($eventId)) {
                continue;
            }

            if (!$eventRegistration->canRegister($event)) {
                continue;
            }

            $events[] = $eventsModuleProxy->getProcessedEvent($event);
        }

        return parent::parse(array_merge($attributes ?? [], ['events' => $events]));
    }

    public function validate(): void
    {
        $this->rgxp = null;

        parent::validate();
    }
}

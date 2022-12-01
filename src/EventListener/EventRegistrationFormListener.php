<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Form;
use Contao\FrontendUser;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Security;

/**
 * Creates a new event registration record.
 * Priority is higher so that it is executed before the notification center.
 *
 * @Hook("processFormData", priority=10)
 */
class EventRegistrationFormListener
{
    private $eventRegistration;
    private $security;

    public function __construct(EventRegistration $eventRegistration, Security $security)
    {
        $this->eventRegistration = $eventRegistration;
        $this->security = $security;
    }

    public function __invoke(array &$submittedData, array $formData, ?array $files, array $labels, Form $form): void
    {
        if (!$this->eventRegistration->isEventRegistrationForm($form)) {
            return;
        }

        $event = $this->getEvent(true);

        if (null === $event || !$this->eventRegistration->canRegister($event)) {
            return;
        }

        $member = $this->getMember();

        // check if registration exists
        $registration = EventRegistrationModel::findOneBy(['pid = ?', 'member = ?'], [(int) $event->id, (int) $member->id]);

        if (null !== $registration) {
            $registration->cancelled = 0;
        }

        if (null === $registration) {
            $registration = new EventRegistrationModel();
            $registration->created = time();
        }

        $registration->pid = (int) $event->id;
        $registration->tstamp = time();
        $registration->uuid = Uuid::uuid4()->toString();
        $registration->form = (int) $form->id;
        $registration->member = $member ? (int) $member->id : 0;
        $registration->amount = (int) $submittedData['amount'] ?: 1;
        $registration->form_data = json_encode($submittedData);

        $registration->save();

        // Inject event registration UUID
        $submittedData['event_registration_uuid'] = $registration->uuid;
    }

    /**
     * Returns the current event, if applicable.
     */
    private function getEvent(bool $returnMainEvent = true): ?CalendarEventsModel
    {
        $event = $this->eventRegistration->getCurrentEvent();

        if (null === $event) {
            return null;
        }

        // Return main event if connected via changelanguage
        if ($returnMainEvent) {
            return $this->eventRegistration->getMainEvent($event);
        }

        return $event;
    }

    /**
     * Returns the current frontend user.
     */
    private function getMember(): ?FrontendUser
    {
        $user = $this->security->getUser();

        if ($user instanceof FrontendUser) {
            return $user;
        }

        return null;
    }
}

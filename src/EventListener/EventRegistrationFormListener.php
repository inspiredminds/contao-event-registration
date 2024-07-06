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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Creates a new event registration record.
 * Priority is higher so that it is executed before the notification center.
 *
 * @Hook("processFormData", priority=10)
 */
class EventRegistrationFormListener
{
    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(array &$submittedData, array $formData, array|null $files, array &$labels, Form $form): void
    {
        if (!$this->eventRegistration->isEventRegistrationForm($form)) {
            return;
        }

        $event = $this->getEvent(true);

        if (!$event || !$this->eventRegistration->canRegister($event)) {
            return;
        }

        $member = $this->getMember();

        $registration = new EventRegistrationModel();
        $registration->pid = (int) $event->id;
        $registration->created = time();
        $registration->tstamp = time();
        $registration->uuid = Uuid::uuid4()->toString();
        $registration->form = (int) $form->id;
        $registration->member = $member ? (int) $member->id : 0;
        $registration->amount = max(1, (int) ($submittedData['amount'] ?? 1));
        $registration->form_data = json_encode($submittedData, JSON_THROW_ON_ERROR);

        $registration->save();

        // Inject event registration UUID
        $t = EventRegistrationModel::getTable();
        $submittedData['event_registration_uuid'] = $registration->uuid;
        $labels['event_registration_uuid'] = $this->translator->trans($t.'.uuid.0', [], 'contao_'.$t);
    }

    /**
     * Returns the current event, if applicable.
     */
    private function getEvent(bool $returnMainEvent = true): CalendarEventsModel|null
    {
        if (!$event = $this->eventRegistration->getCurrentEvent()) {
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
    private function getMember(): FrontendUser|null
    {
        if (($user = $this->tokenStorage->getToken()?->getUser()) instanceof FrontendUser) {
            return $user;
        }

        return null;
    }
}

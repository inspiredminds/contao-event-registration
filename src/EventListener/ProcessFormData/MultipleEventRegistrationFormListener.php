<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\ProcessFormData;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\FrontendUser;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use InspiredMinds\ContaoEventRegistration\WaitingListChecker;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Creates new event registration records.
 * Priority is higher so that it is executed before the notification center.
 */
#[AsHook('processFormData', priority: 10)]
class MultipleEventRegistrationFormListener
{
    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly LockFactory $lockFactory,
    ) {
    }

    public function __invoke(array &$submittedData, array $formData, array|null $files, array &$labels, Form $form): void
    {
        if (!$formFields = FormFieldModel::findByPid($form->id)) {
            return;
        }

        $formField = reset(array_filter($formFields->getModels(), static fn (FormFieldModel $formField): bool => 'event_registration' === $formField->type));

        if (!$formField) {
            return;
        }

        if (!$submittedData[$formField->name] ?? null) {
            return;
        }

        $lock = $this->lockFactory->createLock(WaitingListChecker::class);
        $lock->acquire(true);

        try {
            $eventTitles = [];
            $registrationUuids = [];

            foreach ($submittedData[$formField->name] as $eventId) {
                if (!$event = CalendarEventsModel::findById($eventId)) {
                    continue;
                }

                // Check if the registration is possible for the current event
                if (!$this->eventRegistration->canRegister($event)) {
                    continue;
                }

                // Retrieve the main event for its other settings
                if (!$event = $this->eventRegistration->getMainEvent($event)) {
                    continue;
                }

                $amount = max(1, (int) ($submittedData['amount'] ?? 1));
                $waiting = '' !== (string) $event->reg_max && ($this->eventRegistration->getRegistrationCount($event, true) + $amount) > $event->reg_max;

                $registration = new EventRegistrationModel();
                $registration->pid = (int) $event->id;
                $registration->created = time();
                $registration->tstamp = time();
                $registration->uuid = Uuid::uuid4()->toString();
                $registration->form = (int) $form->id;
                $registration->member = (int) $this->getMember()?->id ?? 0;
                $registration->amount = $amount;
                $registration->waiting = $waiting;
                $registration->form_data = json_encode($submittedData, JSON_THROW_ON_ERROR);

                $registration->save();

                $eventTitles[] = $event->title;
                $registrationUuids[] = $registration->uuid;
            }

            $submittedData[$formField->name] = implode(', ', $eventTitles);
            $submittedData['event_registration_uuids'] = $registrationUuids;

            $t = EventRegistrationModel::getTable();
            $labels['event_registration_uuids'] = $this->translator->trans($t.'.uuid.0', [], 'contao_'.$t);
        } finally {
            $lock->release();
        }
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

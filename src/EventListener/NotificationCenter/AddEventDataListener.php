<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\NotificationCenter;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FormModel;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;
use NotificationCenter\Model\Notification;

/**
 * @Hook("sendNotificationMessage")
 */
class AddEventDataListener
{
    public function __construct(private readonly EventRegistration $eventRegistration)
    {
    }

    public function __invoke(Message $message, array &$tokens, string $language, Gateway $gateway): bool
    {
        // Get the current event, if applicable.
        $event = $this->eventRegistration->getCurrentEvent();

        if (!$event || !$this->isEventRegistrationFormNotification($message)) {
            return true;
        }

        if (empty($tokens['form_event_registration_uuid'])) {
            throw new \RuntimeException('No event registration ID present. Was EventRegistrationFormListener executed before?');
        }

        $registration = EventRegistrationModel::findOneByUuid($tokens['form_event_registration_uuid']);

        if (null === $registration) {
            throw new \RuntimeException('Invalid registration UUID given.');
        }

        $tokens = array_merge($tokens, $this->eventRegistration->getSimpleTokens($event, $registration));

        return true;
    }

    private function isEventRegistrationFormNotification(Message $message): bool
    {
        /** @var Notification $notification */
        $notification = $message->getRelated('pid');

        // Get forms that use this notification
        $forms = FormModel::findBy(['nc_notification = ?'], [$notification->id]);

        if (null === $forms) {
            return false;
        }

        foreach ($forms as $form) {
            if ($this->eventRegistration->isEventRegistrationForm($form)) {
                return true;
            }
        }

        return false;
    }
}

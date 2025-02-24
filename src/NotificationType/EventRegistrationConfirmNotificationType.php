<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\NotificationType;

use InspiredMinds\ContaoEventRegistration\NotificationTypes;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;

class EventRegistrationConfirmNotificationType implements NotificationTypeInterface
{
    public function getName(): string
    {
        return NotificationTypes::CONFIRM;
    }

    public function getTokenDefinitions(): array
    {
        // Will be added by \InspiredMinds\ContaoEventRegistration\EventListener\EventRegistrationTokensListener
        return [];
    }
}

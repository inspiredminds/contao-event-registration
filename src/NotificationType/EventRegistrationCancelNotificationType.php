<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\NotificationType;

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

class EventRegistrationCancelNotificationType extends EventRegistrationConfirmNotificationType
{
    public function getName(): string
    {
        return NotificationTypes::CANCEL;
    }
}

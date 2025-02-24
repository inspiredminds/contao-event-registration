<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\NotificationType;

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

class EventRegistrationWaitingListAdvancementNotificationType extends EventRegistrationConfirmNotificationType
{
    public function getName(): string
    {
        return NotificationTypes::WAITING_LIST_ADVANCEMENT;
    }
}

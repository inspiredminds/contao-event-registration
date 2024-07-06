<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
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

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
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;

class EventRegistrationConfirmNotificationType implements NotificationTypeInterface
{
    public function __construct(private readonly NotificationTypeInterface $formGeneratorNotificationType)
    {
    }

    public function getName(): string
    {
        return NotificationTypes::CONFIRM;
    }

    public function getTokenDefinitions(): array
    {
        return $this->formGeneratorNotificationType->getTokenDefinitions();
    }
}

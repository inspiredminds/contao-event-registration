<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\CalendarEvents;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\NotificationTypes;

#[AsCallback('tl_calendar_events', 'fields.reg_waitingListAdvancementNotification.options')]
class WaitingListAdvancementNotificationOptionsCallbackListener
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function __invoke(): array
    {
        return $this->db->fetchAllKeyValue('SELECT id, title FROM tl_nc_notification WHERE type = ? ORDER BY title', [NotificationTypes::WAITING_LIST_ADVANCEMENT]);
    }
}

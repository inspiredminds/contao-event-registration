<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use InspiredMinds\ContaoEventRegistration\WaitingListChecker;

#[AsCallback('tl_event_registration', 'config.onsubmit')]
class ConfigOnSubmitCallbackListener
{
    public function __construct(private readonly WaitingListChecker $waitingListChecker)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        if ($event = CalendarEventsModel::findById($dc->activeRecord?->pid)) {
            ($this->waitingListChecker)($event);
        }
    }
}

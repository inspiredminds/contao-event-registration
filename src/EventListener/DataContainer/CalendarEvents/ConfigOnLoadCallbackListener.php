<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\CalendarEvents;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use InspiredMinds\ContaoEventRegistration\EventRegistration;

/**
 * Removes some fields if the event is not the main record.
 *
 * @Callback(table="tl_calendar_events", target="config.onload")
 */
class ConfigOnLoadCallbackListener
{
    public function __construct(private readonly EventRegistration $eventRegistration)
    {
    }

    public function __invoke(DataContainer $dc): void
    {
        $event = CalendarEventsModel::findById($dc->id);

        if (null === $event) {
            return;
        }

        $mainEvent = $this->eventRegistration->getMainEvent($event);

        if ((int) $mainEvent->id === (int) $event->id) {
            return;
        }

        PaletteManipulator::create()
            ->removeField('reg_min')
            ->removeField('reg_max')
            ->removeField('reg_regEnd')
            ->removeField('reg_cancelEnd')
            ->removeField('reg_requireConfirm')
            ->removeField('reg_enableWaitingList')
            ->applyToSubPalette('reg_enable', 'tl_calendar_events')
        ;
    }
}

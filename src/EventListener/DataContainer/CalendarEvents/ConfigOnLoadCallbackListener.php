<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\CalendarEvents;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * Removes some fields if the event is not the main record.
 *
 * @Callback(table="tl_calendar_events", target="config.onload")
 */
class ConfigOnLoadCallbackListener
{
    public function __invoke(DataContainer $dc): void
    {
        $event = CalendarEventsModel::findById($dc->id);

        if (null === $event || empty($event->languageMain)) {
            return;
        }

        PaletteManipulator::create()
            ->removeField('reg_min')
            ->removeField('reg_max')
            ->removeField('reg_regEnd')
            ->removeField('reg_cancelEnd')
            ->removeField('reg_requireConfirm')
            ->applyToSubPalette('reg_enable', 'tl_calendar_events')
        ;
    }
}

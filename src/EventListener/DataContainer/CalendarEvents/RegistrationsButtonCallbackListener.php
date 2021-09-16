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

use Contao\Backend;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;

/**
 * Changes the ID to the main record when connected via changelanguage.
 *
 * @Callback(table="tl_calendar_events", target="list.operations.registrations.button")
 */
class RegistrationsButtonCallbackListener
{
    private $eventRegistration;

    public function __construct(EventRegistration $eventRegistration)
    {
        $this->eventRegistration = $eventRegistration;
    }

    public function __invoke(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        $mainEvent = $this->eventRegistration->getMainEvent(CalendarEventsModel::findById($row['id']));

        $href = Backend::addToUrl($href.'&amp;id='.$mainEvent->id.(Input::get('nb') ? '&amp;nc=1' : ''));

        if (0 === EventRegistrationModel::countByPid((int) $mainEvent->id)) {
            $icon = 'mgroup_.svg';
        }

        return '<a href="'.$href.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }
}

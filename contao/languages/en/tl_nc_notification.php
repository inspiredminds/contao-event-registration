<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

$GLOBALS['TL_LANG']['tl_nc_notification']['type']['event_registration'] = 'Event registration';
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CONFIRM] = ['Event Registration: registration confirmed', 'This notification type is sent when an event registration was confirmed.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CANCEL] = ['Event Registration: registration cancelled', 'This notification type is sent when an event registration was cancelled.'];

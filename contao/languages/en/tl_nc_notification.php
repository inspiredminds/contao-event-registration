<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

$GLOBALS['TL_LANG']['tl_nc_notification']['type']['event_registration'] = 'Event registration';
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CONFIRM] = ['Confirm registration', 'This notification type can be sent when an event registration is confirmed.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CANCEL] = ['Cancel registration', 'This notification type can be sent when an event registration is cancelled.'];

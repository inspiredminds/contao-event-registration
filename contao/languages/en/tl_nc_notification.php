<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

$GLOBALS['TL_LANG']['tl_nc_notification']['type']['event_registration'] = 'Event registration';
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CONFIRM] = ['Confirm registration', 'This notification type can be sent when an event registration is confirmed.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CANCEL] = ['Cancel registration', 'This notification type can be sent when an event registration is cancelled.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::WAITING_LIST_ADVANCEMENT] = ['Advancement from waiting list', 'This notification type can be sent when an event registration is cancelled.'];

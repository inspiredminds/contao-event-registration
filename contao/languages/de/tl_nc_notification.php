<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\ContaoEventRegistration\NotificationTypes;

$GLOBALS['TL_LANG']['tl_nc_notification']['type']['event_registration'] = 'Event Registrierung';
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CONFIRM] = ['Registrierung bestätigen', 'Diese Benachrichtigung wird gesendet, wenn eine Registrierung für ein Event bestätigt wurde.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::CANCEL] = ['Registrierung stornieren', 'Diese Benachrichtigung wird gesendet, wenn eine Registrierung für ein Event storniert wurde.'];
$GLOBALS['TL_LANG']['tl_nc_notification']['type'][NotificationTypes::WAITING_LIST_ADVANCEMENT] = ['Aufstieg von Warteliste', 'Diese Benachrichtigung wird gesendet, wenn eine Registrierung von der Warteliste auf die normale Liste kommt.'];

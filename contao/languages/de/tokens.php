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

$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['event_*'] = 'Alle Eigenschaften des Events.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_*'] = 'Alle Eigenschaften der Registrierung.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_count'] = 'Anzahl an Registrierungen für dieses Event.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_confirm_url'] = 'URL für die Bestätigung der Registrierung.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_cancel_url'] = 'URL für die Stornierung der Registrierung.';

$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][NotificationTypes::CONFIRM] = &$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form'];
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][NotificationTypes::CANCEL] = &$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form'];
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][NotificationTypes::WAITING_LIST_ADVANCEMENT] = &$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form'];

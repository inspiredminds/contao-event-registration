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

$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['event_*'] = 'All attributes of the event.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_*'] = 'All attributes of the registration.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_count'] = 'Registration count for this event.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_confirm_url'] = 'URL for confirming a registration.';
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form']['reg_cancel_url'] = 'URL for cancelling a registration.';

$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][NotificationTypes::CONFIRM] = &$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form'];
$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN'][NotificationTypes::CANCEL] = &$GLOBALS['TL_LANG']['NOTIFICATION_CENTER_TOKEN']['core_form'];

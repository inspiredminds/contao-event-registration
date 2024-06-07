<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use InspiredMinds\ContaoEventRegistration\NotificationTypes;

$GLOBALS['TL_MODELS']['tl_event_registration'] = EventRegistrationModel::class;
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_event_registration';

$coreForm = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['contao']['core_form'];

foreach (['email_subject', 'email_text', 'email_html'] as $type) {
    $coreForm[$type][] = 'event_*';
    $coreForm[$type][] = 'reg_*';
    $coreForm[$type][] = 'reg_count';
    $coreForm[$type][] = 'reg_confirm_url';
    $coreForm[$type][] = 'reg_cancel_url';
}

$tokensContent = ['event_*', 'reg_*', 'reg_count', 'reg_confirm_url', 'reg_confirm_url', 'admin_email'];
$tokensAddress = ['admin_email', 'reg_*', 'event_*'];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['event_registration'][NotificationTypes::CONFIRM] = [
    'recipients' => $tokensAddress,
    'email_subject' => $tokensContent,
    'email_text' => $tokensContent,
    'email_html' => $tokensContent,
    'email_sender_name' => $tokensAddress,
    'email_sender_address' => $tokensAddress,
    'email_recipient_cc' => $tokensAddress,
    'email_recipient_bcc' => $tokensAddress,
    'email_replyTo' => $tokensAddress,
];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['event_registration'][NotificationTypes::CANCEL] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['event_registration'][NotificationTypes::CONFIRM];

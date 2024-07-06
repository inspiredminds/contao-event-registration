<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\CalendarEvents;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Callback(table="tl_calendar_events", target="fields.reg_form.options")
 */
class RegistrationFormOptionsCallbackListener
{
    public function __construct(
        private readonly Connection $db,
        private readonly AuthorizationCheckerInterface $auth,
    ) {
    }

    public function __invoke(): array
    {
        if (!$this->auth->isGranted('ROLE_USER')) {
            return [];
        }

        if (!$forms = $this->db->fetchAllKeyValue('SELECT id, title FROM tl_form ORDER BY title')) {
            return [];
        }

        $options = [];
        $isAdmin = $this->auth->isGranted('ROLE_ADMIN');

        foreach ($forms as $formId => $formTitle) {
            if ($isAdmin || $this->auth->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FORM, $formId)) {
                $options[(int) $formId] = $formTitle.' (ID '.$formId.')';
            }
        }

        return $options;
    }
}

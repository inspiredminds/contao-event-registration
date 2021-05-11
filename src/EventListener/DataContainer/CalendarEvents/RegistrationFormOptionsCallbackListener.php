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

use Contao\BackendUser;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;

/**
 * @Callback(table="tl_calendar_events", target="fields.reg_form.options")
 */
class RegistrationFormOptionsCallbackListener
{
    private $db;
    private $security;

    public function __construct(Connection $db, Security $security)
    {
        $this->db = $db;
        $this->security = $security;
    }

    public function __invoke(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin && !\is_array($user->forms)) {
            return [];
        }

        $forms = $this->db->executeQuery("SELECT id, title FROM tl_form ORDER BY title")->fetchAll();

        if (false === $forms) {
            return [];
        }

        $options = [];

        foreach ($forms as $form) {
            if ($user->hasAccess($form['id'], 'forms')) {
                $options[$form['id']] = $form['title'].' (ID '.$form['id'].')';
            }
        }

        return $options;
    }
}

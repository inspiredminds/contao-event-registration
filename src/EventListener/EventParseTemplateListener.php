<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use InspiredMinds\ContaoEventRegistration\EventRegistration;

/**
 * @Hook("parseTemplate")
 */
class EventParseTemplateListener
{
    private $eventRegistration;

    public function __construct(EventRegistration $eventRegistration)
    {
        $this->eventRegistration = $eventRegistration;
    }

    public function __invoke(Template $template): void
    {
        if (empty($template->calendar) || 0 !== strpos($template->getName(), 'event')) {
            return;
        }

        if (!$template->calendar instanceof CalendarModel) {
            return;
        }

        $event = CalendarEventsModel::findById($template->id);

        if (null === $event) {
            return;
        }

        $this->eventRegistration->addTemplateData($template, $event);
    }
}

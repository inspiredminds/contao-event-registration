<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
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
    public function __construct(private readonly EventRegistration $eventRegistration)
    {
    }

    public function __invoke(Template $template): void
    {
        if (empty($template->calendar) || !str_starts_with($template->getName(), 'event')) {
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

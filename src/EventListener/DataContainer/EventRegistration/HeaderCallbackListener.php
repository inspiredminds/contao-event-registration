<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds the registration count to the header.
 *
 * @Callback(table="tl_event_registration", target="list.sorting.header")
 */
class HeaderCallbackListener
{
    public function __construct(
        private readonly EventRegistration $helper,
        private readonly TranslatorInterface $trans,
    ) {
    }

    public function __invoke(array $labels, DataContainer $dc): array
    {
        $event = $this->helper->getMainEvent(CalendarEventsModel::findById($dc->id));
        $count = $this->helper->getRegistrationCount($event, true);
        $waiting = $this->helper->getRegistrationCount($event) - $count;

        $labels[$this->trans->trans('header_count_title', [], 'im_contao_event_registration')] = $count;
        $labels[$this->trans->trans('header_waiting_title', [], 'im_contao_event_registration')] = $waiting;

        return $labels;
    }
}

<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration;

use Contao\CalendarEventsModel;
use Contao\Events;

class EventsModuleProxy extends Events
{
    public function __construct()
    {
    }

    public function getProcessedEvent(CalendarEventsModel|array $event): array
    {
        $this->arrEvents = [];

        if (\is_array($event)) {
            if (!$event = CalendarEventsModel::findById($event['id'])) {
                throw new \RuntimeException('Could not load event.');
            }
        }

        $time = time();

        $this->addEvent($event, $time, $time, $time, PHP_INT_MAX, (int) $event->pid);

        $processedEvent = end(end(end($this->arrEvents)));

        $this->arrEvents = [];

        return $processedEvent;
    }

    protected function compile(): void
    {
        // noop
    }
}

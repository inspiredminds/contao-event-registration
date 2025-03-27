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

    public function getProcessedEvent(CalendarEventsModel|array $event, int|null $begin = null, int|null $end = null): array
    {
        $this->arrEvents = [];

        if (\is_array($event)) {
            if (!$event = CalendarEventsModel::findById($event['id'])) {
                throw new \RuntimeException('Could not load event.');
            }
        }

        $this->addEvent($event, $event->startTime, $event->endTime, $begin ?? time(), $end ?? PHP_INT_MAX, (int) $event->pid);

        $processedEvent = end(end(end($this->arrEvents)));

        $this->arrEvents = [];

        return $processedEvent;
    }

    protected function compile(): void
    {
        // noop
    }
}

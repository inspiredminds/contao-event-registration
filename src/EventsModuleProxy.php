<?php

declare(strict_types=1);

namespace InspiredMinds\ContaoEventRegistration;

use Contao\Events;

class EventsModuleProxy extends Events
{
    public function __construct()
    {
    }

    public function collectAllEvents(array $calendars, int $start, int $end, bool|null $featured = null): array
    {
        return $this->getAllEvents($calendars, $start, $end, $featured);
    }

    protected function compile(): void
    {
        // noop
    }
}

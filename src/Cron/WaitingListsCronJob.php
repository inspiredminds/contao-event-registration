<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use InspiredMinds\ContaoEventRegistration\WaitingListChecker;

#[AsCronJob('hourly')]
class WaitingListsCronJob
{
    public function __construct(private readonly WaitingListChecker $waitingListChecker)
    {
    }

    public function __invoke(): void
    {
        ($this->waitingListChecker)();
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
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

<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Symfony\Component\Lock\LockFactory;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class WaitingListChecker
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly LockFactory $lockFactory,
        private readonly EventRegistration $eventRegistration,
        private readonly NotificationCenter $notificationCenter,
    ) {
    }

    /**
     * @param CalendarEventsModel $event checks a single event, otherwise all upcoming events
     */
    public function __invoke(CalendarEventsModel|null $event = null): void
    {
        $this->framework->initialize();

        $lock = $this->lockFactory->createLock(self::class);
        $lock->acquire(true);

        try {
            // Go through all upcoming events, if no event given
            $events = $event ? [$event] : CalendarEventsModel::findUpcomingByPids(CalendarModel::findAll()?->fetchEach('id') ?? []);

            foreach ($events ?? [] as $event) {
                $event = $this->eventRegistration->getMainEvent($event);

                // Check if waiting list is enabled at all
                if (!$event->reg_enableWaitingList) {
                    continue;
                }

                // Fill up slots with waiting list entries
                while (($diff = $event->reg_max - $this->eventRegistration->getRegistrationCount($event, true)) > 0) {
                    // Get the next waiting registration entry whose amount is equal or smaller to the diff
                    $waitingRegistration = EventRegistrationModel::findOneBy(['pid = ?', 'waiting = 1', 'cancelled != 1', 'amount <= ?'], [$event->id, $diff], ['order' => 'created ASC']);

                    // If there are no waiting registrations, break
                    if (!$waitingRegistration) {
                        break;
                    }

                    $waitingRegistration->waiting = false;
                    $waitingRegistration->save();

                    // Send notification
                    if ($event->reg_waitingListAdvancementNotification) {
                        $this->notificationCenter->sendNotification($event->reg_waitingListAdvancementNotification, $this->eventRegistration->getSimpleTokens($event, $waitingRegistration));
                    }
                }
            }
        } finally {
            $lock->release();
        }
    }
}

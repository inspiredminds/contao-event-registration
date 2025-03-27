<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\NotificationCenter;

use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Token;

#[AsEventListener]
class AddMultipleRegistrationsDataListener
{
    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly Connection $db,
    ) {
    }

    public function __invoke(CreateParcelEvent $createParcelEvent): void
    {
        $parcel = $createParcelEvent->getParcel();
        $tokenCollection = $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection;

        if (!$token = $tokenCollection->getByName('form_event_registration_uuids')) {
            return;
        }

        /** @var list<string> $uuids */
        if (!$uuids = $token->getValue()) {
            return;
        }

        $uuids = array_map(fn (string $uuid): string => $this->db->quote($uuid), $uuids);

        $registrations = EventRegistrationModel::findBy([\sprintf('uuid IN (%s)', implode(',', $uuids))], []);

        if (!$registrations) {
            throw new \RuntimeException('Invalid registration UUIDs given.');
        }

        $eventRegistrationTokens = $this->eventRegistration->getSimpleTokensForMultipleRegistrations($registrations);

        foreach ($eventRegistrationTokens as $name => $value) {
            $tokenCollection->addToken(new Token($name, $value, (string) $value));
        }
    }
}

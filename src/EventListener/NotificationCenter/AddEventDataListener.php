<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\NotificationCenter;

use Contao\FormModel;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Token;

#[AsEventListener]
class AddEventDataListener
{
    public function __construct(
        private readonly EventRegistration $eventRegistration,
        private readonly TokenDefinitionFactoryInterface $factory,
    ) {
    }

    public function __invoke(CreateParcelEvent $createParcelEvent): void
    {
        $parcel = $createParcelEvent->getParcel();
        $messageConfig = $parcel->getMessageConfig();

        // Get the current event, if applicable.
        $calendarEvent = $this->eventRegistration->getCurrentEvent();

        if (!$calendarEvent || !$this->isEventRegistrationFormNotification($messageConfig)) {
            return;
        }

        $tokenCollection = $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection;
        $tokens = $tokenCollection->forSimpleTokenParser();

        if (empty($tokens['form_event_registration_uuid'])) {
            throw new \RuntimeException('No event registration ID present. Was EventRegistrationFormListener executed before?');
        }

        $registration = EventRegistrationModel::findOneByUuid($tokens['form_event_registration_uuid']);

        if (null === $registration) {
            throw new \RuntimeException('Invalid registration UUID given.');
        }

        $eventRegistrationTokens = $this->eventRegistration->getSimpleTokens($calendarEvent, $registration);

        foreach ($eventRegistrationTokens as $name => $value) {
            $tokenCollection->addToken(new Token($name, $value, (string) $value));
        }
    }

    private function isEventRegistrationFormNotification(MessageConfig $messageConfig): bool
    {
        if (!$forms = FormModel::findBy(['nc_notification = ?'], [$messageConfig->getNotification()])) {
            return false;
        }

        foreach ($forms as $form) {
            if ($this->eventRegistration->isEventRegistrationForm($form)) {
                return true;
            }
        }

        return false;
    }
}

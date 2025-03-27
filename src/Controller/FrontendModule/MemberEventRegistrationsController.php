<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use InspiredMinds\ContaoEventRegistration\EventRegistration;
use InspiredMinds\ContaoEventRegistration\EventRegistration\LabelBuilder;
use InspiredMinds\ContaoEventRegistration\EventsModuleProxy;
use InspiredMinds\ContaoEventRegistration\Model\EventRegistrationModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Lists all (confirmed) registrations of the current member.
 */
#[AsFrontendModule(self::TYPE, 'user', 'mod_member_event_registrations')]
class MemberEventRegistrationsController extends AbstractFrontendModuleController
{
    public const TYPE = 'member_event_registrations';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventRegistration $eventRegistration,
        private readonly LabelBuilder $labelBuilder,
        private readonly Connection $db,
        private readonly EventsModuleProxy $eventsModuleProxy,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if (!($member = $this->tokenStorage->getToken()?->getUser()) instanceof FrontendUser) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        if (!$collection = EventRegistrationModel::findBy(['member = ?', 'cancelled != 1'], [(int) $member->id])) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        $registrations = [];

        /** @var EventRegistrationModel $model */
        foreach ($collection as $model) {
            if (!$event = CalendarEventsModel::findById($model->pid)) {
                continue;
            }

            $registration = $model->row();

            if (!isset($registration['label'])) {
                $registration['label'] = ($this->labelBuilder)($registration);
            }

            try {
                $registration['form_data'] = json_decode((string) $registration['form_data'], null, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                // noop
            }

            $registration['confirm_url'] = null;
            $registration['cancel_url'] = $this->eventRegistration->createStatusUpdateUrl($event, $model, EventRegistrationCancelController::ACTION);
            $registration['event'] = $this->eventsModuleProxy->getProcessedEvent($event, $model->created);

            if (!$model->confirmed && $this->eventRegistration->getMainEvent($event)->reg_requireConfirm) {
                $registration['confirm_url'] = $this->eventRegistration->createStatusUpdateUrl($event, $model, EventRegistrationConfirmController::ACTION);
            }

            $registrations[] = $registration;
        }

        $template->registrations = $registrations;

        return $template->getResponse();
    }
}

<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\ContaoEventRegistration\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleCalendar;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(self::TYPE, 'events', priority: 1)]
class EventRegistrationCalendarController extends ModuleCalendar
{
    public const TYPE = 'event_registration_calendar';

    public function __construct()
    {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function compile(): void
    {
        $this->cal_ctemplate = $this->cal_ctemplate_er ?: 'cal_event_registration';

        parent::compile();
    }

    /**
     * @param list<int> $calendarIds
     * @param int       $start
     * @param int       $end
     * @param bool|null $featured
     */
    protected function getAllEvents($calendarIds, $start, $end, $featured = null): array
    {
        // TODO: process events

        return parent::getAllEvents($calendarIds, $start, $end, $featured);
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use InspiredMinds\ContaoEventRegistration\Controller\EventRegistrationExportController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add(EventRegistrationExportController::class, '/contao/eventregistration/export/{eventId}')
        ->controller(EventRegistrationExportController::class)
        ->defaults(['_scope' => 'backend'])
    ;
};

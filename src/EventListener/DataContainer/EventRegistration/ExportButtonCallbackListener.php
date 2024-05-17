<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Event Registration extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoEventRegistration\EventListener\DataContainer\EventRegistration;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\StringUtil;
use InspiredMinds\ContaoEventRegistration\Controller\EventRegistrationExportController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Callback(table="tl_event_registration", target="list.global_operations.export.button")
 */
class ExportButtonCallbackListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(string|null $href, string $label, string $title, string $class, string $attributes): string
    {
        $request = $this->requestStack->getCurrentRequest();

        $href = $this->router->generate(EventRegistrationExportController::class, ['eventId' => (int) $request->query->get('id')]);

        return '<a href="'.$href.'" class="'.$class.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ';
    }
}

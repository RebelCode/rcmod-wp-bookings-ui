<?php

use Dhii\Data\Container\ContainerFactoryInterface;
use RebelCode\Bookings\WordPress\Module\TemplateManager;

/**
 * Function for retrieving array of services definitions.
 *
 * @since [*next-version*]
 *
 * @param \Psr\EventManager\EventManagerInterface $eventManager
 * @param \Dhii\Event\EventFactoryInterface $eventFactory
 * @param ContainerFactoryInterface $containerFactory
 *
 * @return array Services definitions.
 */
return function ($eventManager, $eventFactory, $containerFactory) {
    return [
        'template_manager' => function ($c) use ($eventManager, $eventFactory) {
            $templateManager = new TemplateManager($eventManager, $eventFactory);
            $templateManager->registerTemplates($c->get('wp_bookings_ui/templates'));
            return $templateManager;
        },
        'assets_urls_map' => function ($c) use ($containerFactory) {
            $assetsUrlsMap = require_once $c->get('wp_bookings_ui/assets_urls_map_path');
            return $containerFactory->make([
                ContainerFactoryInterface::K_DATA => $assetsUrlsMap
            ]);
        }
    ];
};
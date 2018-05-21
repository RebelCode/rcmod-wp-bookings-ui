<?php

use Dhii\Data\Container\ContainerFactoryInterface;
use RebelCode\Bookings\WordPress\Module\StatusesHandler;
use RebelCode\Bookings\WordPress\Module\TemplateManager;
use \Psr\EventManager\EventManagerInterface;
use \Dhii\Event\EventFactoryInterface;

/**
 * Function for retrieving array of services definitions.
 *
 * @since [*next-version*]
 *
 * @param EventManagerInterface $eventManager The event manager.
 * @param EventFactoryInterface $eventFactory The event factory.
 * @param ContainerFactoryInterface $containerFactory The container factory.
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
                ContainerFactoryInterface::K_DATA => $assetsUrlsMap,
            ]);
        },
        'eddbk_bookings_ui_statuses_handler' => function ($c) {
            return new StatusesHandler(
                $c->get('booking_logic/statuses'),
                $c->get('wp_bookings_ui/hidden_statuses'),
                $c->get('wp_bookings_ui/statuses_labels'),
                $c->get('wp_bookings_ui/screen_options/key'),
                $c->get('wp_bookings_ui/screen_options/endpoint'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        }
    ];
};

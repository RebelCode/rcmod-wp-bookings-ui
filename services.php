<?php

use Dhii\Cache\MemoryMemoizer;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Output\PlaceholderTemplateFactory;
use Dhii\Output\TemplateFactoryInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\Handlers\GeneralUiStateHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\SaveScreenOptionsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\BookingsStateStatusesHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\BookingsStateStatusTransitionsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\SaveSettingsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\SettingsStateHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\VisibleStatusesHandler;
use RebelCode\Bookings\WordPress\Module\SettingsContainer;
use RebelCode\Bookings\WordPress\Module\TemplateManager;
use \Psr\EventManager\EventManagerInterface;
use \Dhii\Event\EventFactoryInterface;
use RebelCode\Bookings\WordPress\Module\WpNonce;

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
        'eddbk_bookings_ui_state_handler' => function ($c) {
            return new BookingsStateStatusesHandler(
                $c->get('booking_logic/statuses'),
                $c->get('wp_bookings_ui/screen_options/key'),
                $c->get('wp_bookings_ui/screen_options/fields'),
                $c->get('wp_bookings_ui/screen_options/endpoint'),
                $c->get('eddbk_screen_options_cache'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },
        'eddbk_bookings_ui_status_transitions_handler' => function ($c) {
            return new BookingsStateStatusTransitionsHandler(
                $c->get('booking_logic/status_transitions'),
                $c->get('wp_bookings_ui/transitions_labels'),
                $c->get('wp_bookings_ui/hidden_transitions')
            );
        },
        'eddbk_bookings_visible_statuses_handler' => function ($c) {
            return new VisibleStatusesHandler(
                $c->get('wp_bookings_ui/hidden_statuses')
            );
        },
        'eddbk_bookings_save_screen_options_handler' => function ($c) {
            return new SaveScreenOptionsHandler(
                $c->get('wp_bookings_ui/screen_options/key'),
                $c->get('wp_bookings_ui/screen_options/fields'),
                $c->get('eddbk_screen_options_cache')
            );
        },
        'eddbk_screen_options_cache' => function ($c) {
            return new MemoryMemoizer();
        },
        'eddbk_wp_rest_nonce' => function (ContainerInterface $c) {
            return new WpNonce('wp_rest');
        },
        'eddbk_general_ui_state_handler' => function ($c) {
            return new GeneralUiStateHandler(
                $c->get('eddbk_settings_container'),
                $c->get('booking_logic/statuses'),
                $c->get('wp_bookings_ui/statuses_labels'),
                $c->get('wp_bookings_ui/config/currency'),
                $c->get('wp_bookings_ui/config/formats'),
                $c->get('wp_bookings_ui/config/links'),
                $c->get('wp_bookings_ui/ui_actions'),
                $c->get('wp_bookings_ui/validators'),
                $c->get('eddbk_wp_rest_nonce'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },
        'eddbk_settings_container' => function ($c) {
            $settingsPrefix = $c->get('wp_bookings_ui/settings/prefix');
            return new SettingsContainer(
                $c->get($settingsPrefix),
                $c->get('wp_bookings_ui/settings/array_fields'),
                $settingsPrefix
            );
        },
        'eddbk_settings_ui_state_handler' => function ($c) use ($containerFactory) {
            return new SettingsStateHandler(
                $c->get('eddbk_settings_container'),
                $c->get('wp_bookings_ui/settings/options'),
                $c->get('wp_bookings_ui/settings/fields'),
                $c->get('wp_bookings_ui/settings/update_endpoint')
            );
        },
        'eddbk_bookings_update_settings_handler' => function ($c) {
            return new SaveSettingsHandler(
                $c->get('wp_bookings_ui/settings/fields'),
                $c->get('wp_bookings_ui/settings/array_fields'),
                $c->get('wp_bookings_ui/settings/prefix')
            );
        },
        /*
         * The factory used to create templates used in this module.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_template_factory' => function (ContainerInterface $c) {
            return new PlaceholderTemplateFactory(
                'Dhii\Output\PlaceholderTemplate',
                $c->get('wp_bookings_ui/templates_config/token_start'),
                $c->get('wp_bookings_ui/templates_config/token_end'),
                $c->get('wp_bookings_ui/templates_config/token_default')
            );
        },
        /*
         * Function for making templates.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_make_template' => function (ContainerInterface $c) {
            return function ($templateName) use ($c) {
                $templatePath = WP_BOOKINGS_UI_TEMPLATES_DIR . DIRECTORY_SEPARATOR . $templateName;
                $template = file_get_contents($templatePath);
                return $c->get('eddbk_ui_template_factory')->make([
                    TemplateFactoryInterface::K_TEMPLATE => $template
                ]);
            };
        },
        /*
         * The placeholder template for about page.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_about_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('about.html');
        },
        /*
         * The template for settings page.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_settings_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('settings/index.html');
        },
        /*
         * The template for settings page.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_settings_general_tab_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('settings/general.html');
        },
    ];
};

<?php

use Dhii\Cache\MemoryMemoizer;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Output\PlaceholderTemplate;
use Dhii\Output\PlaceholderTemplateFactory;
use Dhii\Output\TemplateFactoryInterface;
use Dhii\Output\TemplateInterface;
use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\Handlers\EnqueueAssetsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\RegisterUiHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\AboutPage;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\BookingsPage;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\ScreenOptions;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\ServiceMetabox;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\ServicesPage;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\SettingsPage;
use RebelCode\Bookings\WordPress\Module\Handlers\AdminBookingsUiServicesHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\FrontApplicationLabelsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\Renderers\StaffMembersPage;
use RebelCode\Bookings\WordPress\Module\Handlers\SaveScreenOptionsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\SaveSettingsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\State\SettingsState;
use RebelCode\Bookings\WordPress\Module\Handlers\State\BookingsState;
use RebelCode\Bookings\WordPress\Module\Handlers\State\BookingsStatusesState;
use RebelCode\Bookings\WordPress\Module\Handlers\State\BookingsTransitionsState;
use RebelCode\Bookings\WordPress\Module\Handlers\State\GeneralState;
use RebelCode\Bookings\WordPress\Module\Handlers\State\ServiceState;
use RebelCode\Bookings\WordPress\Module\Handlers\VisibleStatusesHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\WizardFilterFieldsHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\WizardLabelsHandler;
use RebelCode\Bookings\WordPress\Module\ServiceListTransformer;
use RebelCode\Bookings\WordPress\Module\SettingsContainer;
use RebelCode\Bookings\WordPress\Module\TemplateManager;
use \Psr\EventManager\EventManagerInterface;
use \Dhii\Event\EventFactoryInterface;
use RebelCode\WordPress\Nonce\Factory\NonceFactoryInterface;

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
        /**
         * Event based template manager.
         *
         * @since [*next-version*]
         *
         * @return TemplateManager
         */
        'template_manager' => function ($c) use ($eventManager, $eventFactory) {
            $templateManager = new TemplateManager($eventManager, $eventFactory);
            $templateManager->registerTemplates($c->get('wp_bookings_ui/templates'));

            return $templateManager;
        },

        /**
         * Map of assets urls.
         *
         * @since [*next-version*]
         *
         * @return ContainerInterface
         */
        'assets_urls_map' => function ($c) use ($containerFactory) {
            $assetsUrlsMap = require_once $c->get('wp_bookings_ui/assets_urls_map_path');

            return $containerFactory->make([
                ContainerFactoryInterface::K_DATA => $assetsUrlsMap,
            ]);
        },

        /**
         * Service for registering UI application page.
         *
         * @since [*next-version*]
         *
         * @return RegisterUiHandler
         */
        'eddbk_register_ui_handler' => function ($c) {
            return new RegisterUiHandler(
                $c->get('wp_bookings_ui/menu'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },

        /**
         * Service for handling assets and state on application pages.
         *
         * @since [*next-version*]
         *
         * @return EnqueueAssetsHandler
         */
        'eddbk_enqueue_assets_handler' => function ($c) {
            return new EnqueueAssetsHandler(
                $c->get('assets_urls_map'),
                $c->get('wp_bookings_ui/assets'),
                $c->get('wp_bookings_ui/state_filters'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },

        /**
         * State for bookings application page.
         *
         * @since [*next-version*]
         *
         * @return BookingsState
         */
        'eddbk_bookings_state_handler' => function ($c) {
            return new BookingsState(
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },

        /**
         * Information about statuses for bookings application page.
         *
         * @since [*next-version*]
         *
         * @return BookingsStatusesState
         */
        'eddbk_bookings_statuses_state_handler' => function (ContainerInterface $c) {
            return new BookingsStatusesState(
                $c->get('booking_logic/statuses'),
                $c->get('wp_bookings_ui/screen_options/key'),
                $c->get('wp_bookings_ui/screen_options/fields'),
                $c->get('wp_bookings_ui/screen_options/endpoint'),
                $c->get('eddbk_screen_options_cache'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },

        /**
         * Information about statuses transitions for bookings application page.
         *
         * @since [*next-version*]
         *
         * @return BookingsTransitionsState
         */
        'eddbk_bookings_transitions_state_handler' => function ($c) {
            return new BookingsTransitionsState(
                $c->get('booking_logic/status_transitions'),
                $c->get('wp_bookings_ui/transitions_labels'),
                $c->get('wp_bookings_ui/hidden_transitions')
            );
        },

        /**
         * Renderer handler for the bookings page.
         *
         * @since [*next-version*]
         *
         * @return BookingsPage
         */
        'eddbk_bookings_render_handler' => function ($c) {
            return new BookingsPage(
                $c->get('template_manager')
            );
        },

        /**
         * Renderer handler for the screen options on the bookings page.
         *
         * @since [*next-version*]
         *
         * @return ScreenOptions
         */
        'eddbk_screen_options_render_handler' => function ($c) {
            return new ScreenOptions(
                $c->get('template_manager')
            );
        },

        /**
         * Renderer handler for the settings page.
         *
         * @since [*next-version*]
         *
         * @return SettingsPage
         */
        'eddbk_settings_render_handler' => function ($c) {
            return new SettingsPage(
                $c->get('eddbk_ui_settings_template'),
                $c->get('eddbk_ui_settings_general_tab_template'),
                $c->get('eddbk_ui_settings_wizard_tab_template'),
                $c->get('template_manager')
            );
        },

        /**
         * Renderer handler for the services page.
         *
         * @since [*next-version*]
         *
         * @return ServicesPage
         */
        'eddbk_services_render_handler' => function ($c) {
            return new ServicesPage(
                $c->get('eddbk_ui_services_template'),
                $c->get('template_manager')
            );
        },

        /**
         * Renderer handler for the staff members page.
         *
         * @since [*next-version*]
         *
         * @return ServicesPage
         */
        'eddbk_staff_members_render_handler' => function ($c) {
            return new StaffMembersPage(
                $c->get('eddbk_ui_staff_members_template'),
                $c->get('template_manager')
            );
        },

        /**
         * Renderer handler for the about page.
         *
         * @since [*next-version*]
         *
         * @return AboutPage
         */
        'eddbk_about_render_handler' => function ($c) {
            return new AboutPage(
                $c->get('eddbk_ui_about_template'),
                $c->get('wp_bookings_ui/urls')
            );
        },

        /**
         * Handler for filtering statuses.
         *
         * @since [*next-version*]
         *
         * @return VisibleStatusesHandler
         */
        'eddbk_bookings_visible_statuses_handler' => function ($c) {
            return new VisibleStatusesHandler(
                $c->get('wp_bookings_ui/hidden_statuses')
            );
        },

        /**
         * Handler for saving screen options.
         *
         * @since [*next-version*]
         *
         * @return SaveScreenOptionsHandler
         */
        'eddbk_bookings_save_screen_options_handler' => function ($c) {
            return new SaveScreenOptionsHandler(
                $c->get('wp_bookings_ui/screen_options/key'),
                $c->get('wp_bookings_ui/screen_options/fields'),
                $c->get('eddbk_screen_options_cache')
            );
        },

        /**
         * Cache for screen options.
         *
         * @since [*next-version*]
         *
         * @return MemoryMemoizer
         */
        'eddbk_screen_options_cache' => function ($c) {
            return new MemoryMemoizer();
        },

        /**
         * WP Nonce instance for rest API access.
         *
         * @since [*next-version*]
         *
         * @return WpNonce
         */
        'eddbk_wp_rest_nonce' => function (ContainerInterface $c) {
            return $c->get('eddbk_rest_api_nonce_factory')->make([
                NonceFactoryInterface::K_CONFIG_ID => $c->get('wp_bookings_ui/wp_rest_api_nonce')
            ]);
        },

        /**
         * General state handler.
         *
         * @since [*next-version*]
         *
         * @return GeneralState
         */
        'eddbk_general_state_handler' => function (ContainerInterface $c) {
            return new GeneralState(
                $c->get('eddbk_settings_container'),
                $c->get('booking_logic/statuses'),
                $c->get('wp_bookings_ui/statuses_labels'),
                $c->get('wp_bookings_ui/config'),
                $c->get('wp_bookings_ui/endpoints_config'),
                $c->get('eddbk_wp_rest_nonce'),
                $c->get('event_manager'),
                $c->get('event_factory')
            );
        },

        /**
         * Container for settings of EDDBK plugin.
         *
         * @since [*next-version*]
         *
         * @return SettingsContainer
         */
        'eddbk_settings_container' => function ($c) {
            return new SettingsContainer(
                $c->get('wp_bookings_ui/settings/default_values'),
                $c->get('wp_bookings_ui/settings/array_fields'),
                $c->get('wp_bookings_ui/settings/prefix')
            );
        },
        'eddbk_localized_wizard_labels' => function ($c) {
            $wizardLabelsHandler = new WizardLabelsHandler($c->get('wp_bookings_ui/settings/wizard_labels'));
            return $wizardLabelsHandler();
        },
        'eddbk_front_application_labels_handler' => function ($c) {
            return new FrontApplicationLabelsHandler(
                $c->get('eddbk_settings_container'),
                $c->get('eddbk_localized_wizard_labels')
            );
        },

        /**
         * Settings state handler.
         *
         * @since [*next-version*]
         *
         * @return SettingsState
         */
        'eddbk_settings_state_handler' => function ($c) use ($containerFactory) {
            return new SettingsState(
                $c->get('eddbk_settings_container'),
                $c->get('wp_bookings_ui/settings/options'),
                $c->get('wp_bookings_ui/settings/fields'),
                $c->get('wp_bookings_ui/settings/update_endpoint'),
                $c->get('eddbk_localized_wizard_labels')
            );
        },

        /**
         * Handler for saving settings.
         *
         * @since [*next-version*]
         *
         * @return SaveSettingsHandler
         */
        'eddbk_bookings_update_settings_handler' => function ($c) {
            return new SaveSettingsHandler(
                $c->get('wp_bookings_ui/settings/fields'),
                $c->get('wp_bookings_ui/settings/array_fields'),
                $c->get('wp_bookings_ui/settings/prefix')
            );
        },

        /**
         * The factory used to create templates used in this module.
         *
         * @since [*next-version*]
         *
         * @return PlaceholderTemplateFactory
         */
        'eddbk_ui_template_factory' => function (ContainerInterface $c) {
            return new PlaceholderTemplateFactory(
                'Dhii\Output\PlaceholderTemplate',
                $c->get('wp_bookings_ui/templates_config/token_start'),
                $c->get('wp_bookings_ui/templates_config/token_end'),
                $c->get('wp_bookings_ui/templates_config/token_default')
            );
        },

        /**
         * Function for making templates.
         *
         * @since [*next-version*]
         *
         * @return callable
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

        /**
         * The placeholder template for about page.
         *
         * @since [*next-version*]
         *
         * @return TemplateInterface
         */
        'eddbk_ui_about_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('about.html');
        },

        /**
         * The template for settings page.
         *
         * @since [*next-version*]
         *
         * @return TemplateInterface
         */
        'eddbk_ui_settings_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('settings/index.html');
        },

        /**
         * The template for the services page.
         *
         * @since [*next-version*]
         *
         * @return PlaceholderTemplate
         */
        'eddbk_ui_services_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('services/index.html');
        },

        /**
         * The template for the staff members page.
         *
         * @since [*next-version*]
         *
         * @return PlaceholderTemplate
         */
        'eddbk_ui_staff_members_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('staff_members/index.html');
        },

        /**
         * The template for settings page.
         *
         * @since [*next-version*]
         *
         * @return TemplateInterface
         */
        'eddbk_ui_settings_general_tab_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('settings/general.html');
        },

        /**
         * The template for settings page.
         *
         * @since [*next-version*]
         */
        'eddbk_ui_settings_wizard_tab_template' => function (ContainerInterface $c) {
            $makeTemplateFunction = $c->get('eddbk_ui_make_template');
            return $makeTemplateFunction('settings/wizard.html');
        },

        /**
         * The handler for providing services to the admin bookings UI.
         *
         * @since [*next-version*]
         */
        'eddbk_bookings_ui_services_handler' => function (ContainerInterface $c) {
            return new AdminBookingsUiServicesHandler(
                $c->get('eddbk_services_manager'),
                $c->get('eddbk_bookings_ui_service_list_transformer')
            );
        },

        /**
         * The transformer for transforming lists of services.
         *
         * @since [*next-version*]
         */
        'eddbk_bookings_ui_service_list_transformer' => function (ContainerInterface $c) {
            return new ServiceListTransformer($c->get('eddbk_bookings_ui_service_transformer'));
        },

        /**
         * The service transformer to use for the bookings UI.
         *
         * @since [*next-version*]
         */
        'eddbk_bookings_ui_service_transformer' => function (ContainerInterface $c) {
            // Use the same transformer as the REST API when the request is authenticated
            return $c->get('eddbk_rest_api_full_info_service_transformer');
        },

        /**
         * Filter fields handler.
         *
         * @return WizardFilterFieldsHandler
         */
        'eddbk_front_application_filter_fields_handler' => function ($c) {
            return new WizardFilterFieldsHandler(
                $c->get('eddbk_settings_container')
            );
        },
    ];
};

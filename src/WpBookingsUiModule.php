<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Class WpBookingsUiModule
 *
 * Responsible for providing UI in the dashboard.
 *
 * @since [*next-version*]
 */
class WpBookingsUiModule extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /**
     * Registered booking's page ID.
     *
     * @var string
     */
    protected $bookingsPageId;

    /**
     * Page where metabox application should be shown.
     *
     * @var string
     */
    protected $metaboxPageId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key The module key.
     * @param string[]|Stringable[] $dependencies The module  dependencies.
     * @param ContainerFactoryInterface $configFactory The config factory.
     * @param ContainerFactoryInterface $containerFactory The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface $eventManager The event manager.
     * @param EventFactoryInterface $eventFactory The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    )
    {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(WP_BOOKINGS_UI_MODULE_CONFIG_FILE),
            [
                'template_manager' => function ($c) {
                    $templateManager = new TemplateManager($this->eventManager, $this->eventFactory);
                    $templateManager->registerTemplates($c->get('wp_bookings_ui/templates'));
                    return $templateManager;
                },
                'assets_urls_map' => function ($c) {
                    $assetsUrlsMap = require_once $c->get('wp_bookings_ui/assets_urls_map_path');
                    return $this->_getContainerFactory()->make([
                        ContainerFactoryInterface::K_DATA => $assetsUrlsMap
                    ]);
                }
            ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->metaboxPageId = $c->get('wp_bookings_ui/metabox/post_type');

        /** @var TemplateManager $templateManager */
        $templateManager = $c->get('template_manager');

        $assetsConfig = $c->get('assets_urls_map');

        $this->_attach('admin_enqueue_scripts', function () use ($assetsConfig, $c) {
            $this->_enqueueAssets($assetsConfig, $c);
        }, 999);

        $this->_attach('admin_init', function () use ($templateManager, $c) {
            $this->_adminInit($templateManager, $c);
        });

        $this->_attach('admin_menu', function () use ($templateManager, $c) {
            $this->_adminMenu($templateManager, $c);
        });

        $statusesOptionKey = $c->get('wp_bookings_ui/screen_options/key');
        $this->_attach('wp_ajax_set_' . $statusesOptionKey, function () use ($statusesOptionKey) {
            $data = json_decode(file_get_contents('php://input'), true);
            $statuses = $data['statuses'];
            $this->_setScreenStatuses($statusesOptionKey, $statuses);
        });
    }

    /**
     * Check current screen is screen where EDDBK UI should be rendered.
     *
     * @since [*next-version*]
     *
     * @return bool
     */
    protected function _isOnAppPage()
    {
        return in_array(get_current_screen()->id, [
            $this->bookingsPageId,
            $this->metaboxPageId,
        ]);
    }

    /**
     * Check current screen is bookings page.
     *
     * @since [*next-version*]
     *
     * @return bool
     */
    protected function _isOnBookingsPage()
    {
        return get_current_screen()->id === $this->bookingsPageId;
    }

    /**
     * Get app state for booking page.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c Configuration container of module.
     * @return array Front-end application's state on bookings page.
     */
    protected function _getBookingsAppState($c)
    {
        return [
            /*
             * All available statuses in application.
             */
            'statuses' => $this->_trigger('eddbk_bookings_translated_statuses', [
                'statuses' => $this->_getTranslatedStatuses($c->get('booking_logic/statuses'), $c->get('wp_bookings_ui/statuses_labels'))
            ])->getParam('statuses'),

            /*
             * Statuses that enabled for filtering bookings.
             */
            'screenStatuses' => $this->_trigger('eddbk_bookings_screen_statuses', [
                'screenStatuses' => $this->_getScreenStatuses($c->get('wp_bookings_ui/screen_options/key'), $c->get('booking_logic/statuses'))
            ])->getParam('screenStatuses'),

            /*
             * List of available services.
             */
            'services' => $this->_getListOfServices(),

            'statusesEndpoint' => $c->get('wp_bookings_ui/screen_options/endpoint'),

            'endpointsConfig' => $this->_prepareEndpoints($c->get('wp_bookings_ui/endpoints_config'))
        ];
    }

    /**
     * Get all translated statuses.
     *
     * @since [*next-version*]
     *
     * @param mixed $statuses List of statuses
     * @param mixed $statusesLabels Map of statuses and it's labels
     *
     * @return array Map of statuse codes and translations.
     */
    protected function _getTranslatedStatuses($statuses, $statusesLabels)
    {
        $translatedStatuses = [];

        foreach ($statuses as $status) {
            if ($status === 'none') {
                continue;
            }
            $translatedStatuses[$status] = $this->_containerHas($statusesLabels, $status)
                ? $this->__($this->_containerGet($statusesLabels, $status))
                : $status;
        }

        return $translatedStatuses;
    }

    /**
     * Get list of all services.
     *
     * @since [*next-version*]
     *
     * @return array List of all services.
     */
    protected function _getListOfServices()
    {
        return $this->_trigger('eddbk_admin_bookings_ui_services', [
            'services' => []
        ])->getParam('services');
    }

    /**
     * Prepare endpoints for consuming in the UI.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $endpointsConfig Configuration of endpoints to be prepared.
     * @return array Prepared array of endpoints to use in front-end application.
     */
    protected function _prepareEndpoints($endpointsConfig)
    {
        $resultingConfig = [];

        foreach ($endpointsConfig as $namespace => $endpoints) {
            $resultingConfig[$namespace] = [];
            foreach ($endpoints as $purpose => $endpoint) {
                $endpointUrl = $endpoint->get('endpoint');

                $resultingConfig[$namespace][$purpose] = [
                    'method' => $endpoint->get('method'),
                    'endpoint' => $endpointUrl
                ];

                if ($endpointUrl[0] === '/') {
                    $resultingConfig[$namespace][$purpose]['endpoint'] = rest_url($endpointUrl);
                }
            }
        }

        return $resultingConfig;
    }

    /**
     * Save visible screen statuses in per-user options.
     *
     * @since [*next-version*]
     *
     * @param string $key Key of option where statuses stored.
     * @param string[] $statuses List of statuses to save.
     */
    protected function _setScreenStatuses($key, $statuses)
    {
        if (!($user = wp_get_current_user())) {
            wp_die('0');
        }

        update_user_option(
            $user->ID,
            $key,
            json_encode($statuses)
        );

        wp_die('1');
    }

    /**
     * Return list of all statuses that will be shown for user by default.
     *
     * @since [*next-version*]
     *
     * @param string $key Screen statuses option key.
     * @param string[] $defaultStatuses Array of statuses selected by default
     *
     * @return string[] List of statuses that user selected to show by default
     */
    protected function _getScreenStatuses($key, $defaultStatuses = [])
    {
        if (!$user = wp_get_current_user())
            return [];

        $screenOptions = get_user_option($key, $user->ID);
        if (!$screenOptions) {
            return $defaultStatuses;
        }
        $screenOptions = json_decode($screenOptions);

        return $screenOptions;
    }

    /**
     * Get app state for service page.
     *
     * @since [*next-version*]
     *
     * @return array Front-end application's state on service's page.
     */
    protected function _getServiceAppState()
    {
        $pageId = get_post()->ID;

        return $this->_trigger('eddbk_services_nedit_ui_state', [
            'id' => $pageId,

            /*
             * List of availabilities for current service.
             */
            'availabilities' => [],

            /*
             * List of available sessions for current service.
             */
            'sessions' => [],

            /*
             * Display options settings for current service.
             */
            'displayOptions' => [
                'useCustomerTimezone' => false
            ],
        ])->getParams();
    }

    /**
     * Enqueue all UI assets.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $assetsConfig Assets container config.
     * @param ContainerInterface $c Configuration container of module.
     */
    protected function _enqueueAssets(ContainerInterface $assetsUrlMap, ContainerInterface $c)
    {
        if (!$this->_isOnAppPage()) {
            return;
        }

        /*
         * Enqueue require-related script and script list from the container
         */
        wp_enqueue_script('rc-app-require', $assetsUrlMap->get(
            $c->get('wp_bookings_ui/assets/require.js')
        ), [], false, true);

        wp_localize_script('rc-app-require', 'RC_APP_REQUIRE_FILES', [
            'app' => $assetsUrlMap->get(
                $c->get('wp_bookings_ui/assets/bookings/app.min.js')
            )
        ]);

        /*
         * All application components located here
         */
        wp_enqueue_script('rc-app', $assetsUrlMap->get(
            $c->get('wp_bookings_ui/assets/bookings/main.js')
        ), [], false, true);

        /*
         * Enqueue all styles from assets URL map
         */
        foreach ($c->get('wp_bookings_ui/assets/styles') as $styleId => $styleDependency) {
            wp_enqueue_style('rc-app-' . $styleId, $assetsUrlMap->get($styleDependency));
        }

        $state = $this->_isOnBookingsPage() ? $this->_getBookingsAppState($c) : $this->_getServiceAppState();

        wp_localize_script('rc-app', 'EDDBK_APP_STATE', $state);
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @param ContainerInterface $c Configuration container of module.
     */
    protected function _adminInit($templateManager, $c)
    {
        $metaboxConfig = $c->get('wp_bookings_ui/metabox');
        /*
         * Add metabox with availabilities configuration to
         * service's edit page.
         */
        add_meta_box(
            $metaboxConfig->get('id'),
            $this->__($metaboxConfig->get('title')),
            function () use ($templateManager) {
                echo $this->_renderMetabox($templateManager);
            },
            $metaboxConfig->get('post_type')
        );

        /*
         * Add screen options on bookings management page.
         */
        add_filter('screen_settings', function ($settings, \WP_Screen $screen) use ($templateManager) {
            if (!$this->_isOnBookingsPage())
                return $settings;

            return $this->_renderBookingsScreenOptions($templateManager);
        }, 10, 2);

        /*
         * @todo: this is not working as expected (nothing happens).
         */
//        $this->_attach('screen_settings', function ($event) use ($templateManager) {
//            if (!$this->_isOnBookingsPage())
//                return $event->getParam(0);
//
//            return $this->_renderBookingsScreenOptions($templateManager);
//        }, 10);
    }

    /**
     * Register pages in the admin menu.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @param ContainerInterface $c Configuration container of module.
     */
    protected function _adminMenu($templateManager, $c)
    {
        $rootMenuConfig = $c->get('wp_bookings_ui/menu/root');
        $settingsMenuConfig = $c->get('wp_bookings_ui/menu/settings');
        $aboutMenuConfig = $c->get('wp_bookings_ui/menu/about');

        $this->bookingsPageId = add_menu_page(
            $this->__($rootMenuConfig->get('page_title')),
            $this->__($rootMenuConfig->get('menu_title')),
            $rootMenuConfig->get('capability'),
            $rootMenuConfig->get('menu_slug'),
            function () use ($templateManager) {
                echo $this->_renderMainPage($templateManager);
            },
            $rootMenuConfig->get('icon'),
            $rootMenuConfig->get('position')
        );

        add_submenu_page(
            $rootMenuConfig->get('menu_slug'),
            $this->__($settingsMenuConfig->get('page_title')),
            $this->__($settingsMenuConfig->get('menu_title')),
            $settingsMenuConfig->get('capability'),
            $settingsMenuConfig->get('menu_slug'),
            function () use ($templateManager) {
                return $this->_renderSettingsPage($templateManager);
            }
        );

        add_submenu_page(
            $rootMenuConfig->get('menu_slug'),
            $this->__($aboutMenuConfig->get('page_title')),
            $this->__($aboutMenuConfig->get('menu_title')),
            $aboutMenuConfig->get('capability'),
            $aboutMenuConfig->get('menu_slug'),
            function () use ($templateManager) {
                return $this->_renderAboutPage($templateManager);
            }
        );
    }

    /**
     * Render screen options
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @return string
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _renderBookingsScreenOptions(TemplateManager $templateManager)
    {
        return $templateManager->render('booking/screen-options');
    }

    /**
     * Register metabox for service's bookings settings.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @return string
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _renderMetabox($templateManager)
    {
        return $templateManager->render('availability/metabox');
    }

    /**
     * Render main booking view.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @return mixed
     */
    protected function _renderMainPage($templateManager)
    {
        return $templateManager->render('booking/bookings-page');
    }

    /**
     * Render settings page.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @return string
     * @throws \Exception
     */
    protected function _renderSettingsPage($templateManager)
    {
        throw new \Exception('Implement Settings page.');
    }

    /**
     * Render about page.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager
     * @return string
     * @throws \Exception
     */
    protected function _renderAboutPage($templateManager)
    {
        throw new \Exception('Implement About page.');
    }
}

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
 * Class WpBookingsUiModule.
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
     * Helper class to render templates using events mechanism.
     *
     * @var TemplateManager
     */
    protected $templateManager;

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
     * Page where settings application should be shown.
     *
     * @var string
     */
    protected $settingsPageId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module  dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
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
            $this->_getServicesDefinitions()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->metaboxPageId   = $c->get('wp_bookings_ui/metabox/post_type');
        $this->templateManager = $c->get('template_manager');

        $assetsConfig = $c->get('assets_urls_map');

        $this->_attach('admin_enqueue_scripts', function () use ($assetsConfig, $c) {
            $this->_enqueueAssets($assetsConfig, $c);
        }, 999);

        $this->_attach('admin_init', function () use ($c) {
            $this->_adminInit($c);
        });

        $this->_attach('admin_menu', function () use ($c) {
            $this->_adminMenu($c);
        });

        $this->_attach('eddbk_bookings_ui_state', $c->get('eddbk_bookings_ui_state_handler'));

        $this->_attach('eddbk_bookings_ui_state', $c->get('eddbk_bookings_ui_status_transitions_handler'));

        $this->_attach('eddbk_bookings_visible_statuses', $c->get('eddbk_bookings_visible_statuses_handler'));

        $this->_attach('eddbk_general_ui_state', $c->get('eddbk_general_ui_state_handler'));

        $this->_attach('eddbk_settings_ui_state', $c->get('eddbk_settings_ui_state_handler'));

        $this->_attach('wp_ajax_set_' . $c->get('wp_bookings_ui/screen_options/key'), $c->get('eddbk_bookings_save_screen_options_handler'));
    }

    /**
     * Get services definitions.
     *
     * @since [*next-version*]
     *
     * @return array Services definitions.
     */
    protected function _getServicesDefinitions()
    {
        $definitions = require_once WP_BOOKINGS_UI_MODULE_DEFINITIONS_PATH;

        return $definitions($this->eventManager, $this->eventFactory, $this->_getContainerFactory());
    }

    /**
     * Check current screen is screen where EDDBK UI should be rendered.
     *
     * @since [*next-version*]
     *
     * @return bool Is current page is a page where application should be rendered.
     */
    protected function _isOnAppPage()
    {
        return in_array($this->_getCurrentScreenId(), [
            $this->bookingsPageId,
            $this->metaboxPageId,
            $this->settingsPageId,
        ]);
    }

    /**
     * Check current screen is page.
     *
     * @since [*next-version*]
     *
     * @param int|string $pageId Page ID to check.
     *
     * @return bool Is current page is a some page.
     */
    protected function _isOnPage($pageId)
    {
        return $this->_getCurrentScreenId() === $pageId;
    }

    /**
     * Get current screen identifier.
     *
     * @since [*next-version*]
     *
     * @return int|string Screen ID.
     */
    protected function _getCurrentScreenId()
    {
        return get_current_screen()->id;
    }

    /**
     * Get app state for booking page.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c Configuration container of module.
     *
     * @return array Front-end application's state on bookings page.
     */
    protected function _getBookingsAppState($c)
    {
        return $this->_trigger('eddbk_bookings_ui_state', [
            /*
             * List of available services.
             */
            'services' => $this->_getServices(),

            'endpointsConfig' => $this->_prepareEndpoints($c->get('wp_bookings_ui/endpoints_config')),
        ])->getParams();
    }

    /**
     * Get list of all services.
     *
     * @since [*next-version*]
     *
     * @return array List of all services.
     */
    protected function _getServices()
    {
        return $this->_trigger('eddbk_admin_bookings_ui_services', [
            'services' => [],
        ])->getParam('services');
    }

    /**
     * Prepare endpoints for consuming in the UI.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $endpointsConfig Configuration of endpoints to be prepared.
     *
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
                    'method'   => $endpoint->get('method'),
                    'endpoint' => $endpointUrl,
                ];

                if ($endpointUrl[0] === '/') {
                    $resultingConfig[$namespace][$purpose]['endpoint'] = rest_url($endpointUrl);
                }
            }
        }

        return $resultingConfig;
    }

    /**
     * Get app state for settings page.
     *
     * @since [*next-version*]
     *
     * @return array Front-end application's state on settings's page.
     */
    protected function _getSettingsAppState()
    {
        return $this->_trigger('eddbk_settings_ui_state', [
            'settingsUi' => [
                'preview' => [],
                'options' => [],
                'values'  => [],
            ],
        ])->getParams();
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
             * Service timezone
             */
            'timezone' => null,

            /*
             * Is bookings available for service
             */
            'bookingsEnabled' => false,

            /*
             * List of availabilities for current service.
             */
            'availabilities' => [
                'rules' => [],
            ],

            /*
             * List of available sessions for current service.
             */
            'sessionLengths' => [],

            /*
             * Display options settings for current service.
             */
            'displayOptions' => [
                'useCustomerTimezone' => false,
            ],
        ])->getParams();
    }

    /**
     * Get application state with general data.
     *
     * @since [*next-version*]
     * 
     * @param array $concreteAppState Concrete state of application for page.
     * 
     * @return array Application state with general items.
     */
    protected function _getAppState(array $concreteAppState)
    {
        return array_merge($this->_getGeneralAppState(), $concreteAppState);
    }

    /**
     * Get items in state available for all application parts.
     *
     * @since [*next-version*]
     * 
     * @return array General items in state, that available across all application.
     */
    protected function _getGeneralAppState()
    {
        return $this->_trigger('eddbk_general_ui_state')->getParams();
    }

    /**
     * Enqueue all UI assets.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $assetsConfig Assets container config.
     * @param ContainerInterface $c            Configuration container of module.
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
            ),
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

        $currentScreenId = $this->_getCurrentScreenId();
        switch ($currentScreenId) {
            case $this->bookingsPageId:
                $state = $this->_getBookingsAppState($c);
                break;
            case $this->metaboxPageId:
                $state = $this->_getServiceAppState();
                break;
            case $this->settingsPageId:
                $state = $this->_getSettingsAppState();
                break;
            default:
                $state = [];
        }
        $state = $this->_getAppState($state);

        wp_localize_script('rc-app', 'EDDBK_APP_STATE', $state);
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c Configuration container of module.
     */
    protected function _adminInit($c)
    {
        $metaboxConfig = $c->get('wp_bookings_ui/metabox');
        /*
         * Add metabox with availabilities configuration to
         * service's edit page.
         */
        add_meta_box(
            $metaboxConfig->get('id'),
            $this->__($metaboxConfig->get('title')),
            function () {
                echo $this->_renderTemplate('availability/metabox');
            },
            $metaboxConfig->get('post_type')
        );

        /*
         * Add screen options on bookings management page.
         */
        $this->_attach('screen_settings', function ($event) {
            if (!$this->_isOnPage($this->bookingsPageId)) {
                return $event->getParam(0);
            }
            $event->setParams([
                $this->_renderTemplate('booking/screen-options'),
            ]);
        });
    }

    /**
     * Register pages in the admin menu.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $c Configuration container of module.
     */
    protected function _adminMenu($c)
    {
        $rootMenuConfig     = $c->get('wp_bookings_ui/menu/root');
        $settingsMenuConfig = $c->get('wp_bookings_ui/menu/settings');
        $aboutMenuConfig    = $c->get('wp_bookings_ui/menu/about');

        $comingSoonContext = [
            'beta_guidelines_url' => $c->get('wp_bookings_ui/urls/beta_guidelines'),
            'beta_docs_url'       => $c->get('wp_bookings_ui/urls/beta_docs'),
        ];

        $this->bookingsPageId = add_menu_page(
            $this->__($rootMenuConfig->get('page_title')),
            $this->__($rootMenuConfig->get('menu_title')),
            $rootMenuConfig->get('capability'),
            $rootMenuConfig->get('menu_slug'),
            function () {
                echo $this->_renderTemplate('booking/bookings-page');
            },
            $rootMenuConfig->get('icon'),
            $rootMenuConfig->get('position')
        );

        $this->settingsPageId = add_submenu_page(
            $rootMenuConfig->get('menu_slug'),
            $this->__($settingsMenuConfig->get('page_title')),
            $this->__($settingsMenuConfig->get('menu_title')),
            $settingsMenuConfig->get('capability'),
            $settingsMenuConfig->get('menu_slug'),
            function () use ($c) {
                $settingsTemplate = $c->get('eddbk_ui_settings_template');

                $generalSettingsTabContent = $c->get('eddbk_ui_settings_general_tab_template')->render();
                $componentsContent = $this->_renderTemplate('components');

                echo $settingsTemplate->render([
                    'generalSettingsTab' => $generalSettingsTabContent,
                    'components'         => $componentsContent,
                ]);
            }
        );

        add_submenu_page(
            $rootMenuConfig->get('menu_slug'),
            $this->__($aboutMenuConfig->get('page_title')),
            $this->__($aboutMenuConfig->get('menu_title')),
            $aboutMenuConfig->get('capability'),
            $aboutMenuConfig->get('menu_slug'),
            function () use ($c, $comingSoonContext) {
                $comingSoonTemplate = $c->get('eddbk_ui_coming_soon_template');
                echo $comingSoonTemplate->render($comingSoonContext);
            }
        );
    }

    /**
     * Render given template using template manager.
     *
     * @since [*next-version*]
     *
     * @param string $templateName Relative name of template to render.
     *
     * @return string Rendered template.
     */
    protected function _renderTemplate($templateName)
    {
        return $this->templateManager->render($templateName);
    }
}

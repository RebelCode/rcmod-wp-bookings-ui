<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

class WpBookingsUiModule extends AbstractBaseModule
{
    use ContainerGetCapableTrait;

    use CreateContainerExceptionCapableTrait;

    use CreateNotFoundExceptionCapableTrait;

    use NormalizeKeyCapableTrait;

    private $bookingsPageId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param $key
     * @param ContainerFactoryInterface $containerFactory The factory for creating container instances.
     * @param $eventManager
     * @param $eventFactory
     * @throws \Dhii\Exception\InternalException
     */
    public function __construct($key, ContainerFactoryInterface $containerFactory, $eventManager, $eventFactory)
    {
        $this->_initModule(
            $containerFactory,
            $key,
            [],
            $this->_loadPhpConfigFile(WP_BOOKINGS_UI_MODULE_DIR . '/config.php')
        );

        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * @inheritdoc
     */
    public function setup()
    {
        return $this->_createContainer([
            'template_manager' => function () {
                $templateManager = new TemplateManager($this->eventManager, $this->eventFactory);
                $templateManager->registerTemplates($this->_getConfig()['templates']);
                return $templateManager;
            }
        ]);
    }

    /**
     * @inheritdoc
     */
    public function run(ContainerInterface $c = null)
    {
        /* @var EventManagerInterface $eventManager  */
        $eventManager = $c->get('event_manager');

        /** @var TemplateManager $templateManager */
        $templateManager = $c->get('template_manager');

        $assetsContainer = $this->_getContainerFactory()->make([
            'definitions' => $this->_containerGet($this->_getConfig(), 'assets')
        ]);

        $eventManager->attach('admin_enqueue_scripts', function () use ($assetsContainer) {
            $this->_enqueueAssets($assetsContainer);
        }, 999);

        $eventManager->attach('admin_init', function () use ($eventManager, $templateManager) {
            $this->_adminInit($eventManager, $templateManager);
        });

        $eventManager->attach('admin_menu', function () use ($templateManager) {
            $this->_adminMenu($templateManager);
        });
    }

    /**
     * Check current screen is screen where EDDBK UI should be rendered.
     *
     * @return bool
     */
    protected function _onAppPage()
    {
        return in_array(get_current_screen()->id, [
            $this->bookingsPageId,
            $this->_getConfig()['metabox']['post_type']
        ]);
    }

    /**
     * Check current screen is bookings page.
     *
     * @return bool
     */
    protected function _onBookingsPage()
    {
        return get_current_screen()->id === $this->bookingsPageId;
    }

    /**
     * Get app state for booking page.
     */
    protected function _getBookingsAppState()
    {
        return [
            /*
             * Statuses that enabled for filtering bookings.
             */
            'screenStatuses' => $this->_trigger('eddbk_bookings_screen_statuses', [
                'screenStatuses' => [
                    "draft", "approved", "scheduled", "pending", "completed"
                ]
            ])->getParam('screenStatuses'),

            /*
             * List of available services.
             */
            'services' => $this->_trigger('eddbk_bookings_services', [
                'services' => []
            ])->getParam('services'),

            'endpointsConfig' => $this->_getConfig()['endpointsConfig']
        ];
    }

    /**
     * Get app state for service page
     *
     * @return array
     */
    protected function _getServiceAppState()
    {
        $pageId = get_post()->ID;

        return [
            /*
             * List of availabilities for current service.
             */
            'availabilities' => $this->_trigger('eddbk_service_availabilities', [
                'id' => $pageId,
                'availabilities' => []
            ])->getParam('availabilities'),

            /*
             * List of available sessions for current service.
             */
            'sessions' => $this->_trigger('eddbk_service_sessions', [
                'id' => $pageId,
                'sessions' => []
            ])->getParam('sessions'),

            /*
             * Display options settings for current service.
             */
            'displayOptions' => $this->_trigger('eddbk_service_display_options', [
                'id' => $pageId,
                'displayOptions' => [
                    'useCustomerTimezone' => false
                ]
            ])->getParam('displayOptions'),
        ];
    }

    /**
     * Enqueue all UI assets.
     *
     * @param ContainerInterface $c Assets container
     */
    protected function _enqueueAssets(ContainerInterface $c)
    {
        if (!$this->_onAppPage()) {
            return;
        }

        wp_enqueue_script('rc-app-require', $c->get('require'), [], false, true);

        wp_localize_script('rc-app-require', 'RC_APP_REQUIRE_FILES', [
            'app' => $c->get('bookings_ui/dist/js/app.min.js'),
            'uiFramework' => $c->get('bookings_ui/dist/js/uiFramework.min.js')
        ]);

        /*
         * All application components located here
         */
        wp_enqueue_script('rc-app', $c->get('bookings_ui/assets/js/main.js'), [], false, true);
        wp_enqueue_style('rc-app', $c->get('bookings_ui/dist/wp-booking-ui.css'));

        foreach ($c->get('style_deps') as $styleDep) {
            wp_enqueue_style('rc-app-require', $styleDep);
        }

        $state = $this->_onBookingsPage() ? $this->_getBookingsAppState() : $this->_getServiceAppState();

        wp_localize_script('rc-app', 'EDDBK_APP_STATE', $state);
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @param EventManagerInterface $eventManager
     * @param TemplateManager $templateManager
     */
    protected function _adminInit($eventManager, $templateManager)
    {
        /*
         * Add metabox with availabilities configuration to
         * service's edit page.
         */
        add_meta_box(
            $this->_getConfig()['metabox']['id'],
            $this->_getConfig()['metabox']['title'],
            function () use ($templateManager) {
                echo $this->_renderMetabox($templateManager);
            },
            $this->_getConfig()['metabox']['post_type']
        );

        /*
         * Add screen options on bookings management page.
         */
        add_filter('screen_settings', function( $settings, \WP_Screen $screen ) use ($templateManager) {
            if (!$this->_onBookingsPage())
                return $settings;

            return $this->_renderBookingsScreenOptions($templateManager);
        }, 10, 2);

        /*
         * @todo: this is not working as expected (nothing happens).
         */
//        $this->_attach('screen_settings', function ($event) use ($templateManager) {
//            if (!$this->_onBookingsPage())
//                return $event->getParam(0);
//
//            return $this->_renderBookingsScreenOptions($templateManager);
//        }, 10);
    }

    /**
     * Register pages in the admin menu.
     *
     * @param TemplateManager $templateManager
     */
    protected function _adminMenu(TemplateManager $templateManager)
    {
        $this->bookingsPageId = add_menu_page(
            $this->_getConfig()['menu']['root']['page_title'],
            $this->_getConfig()['menu']['root']['menu_title'],
            $this->_getConfig()['menu']['root']['capability'],
            $this->_getConfig()['menu']['root']['menu_slug'],
            function () use ($templateManager) {
                echo $this->_renderMainPage($templateManager);
            },
            $this->_getConfig()['menu']['root']['icon'],
            $this->_getConfig()['menu']['root']['position']
        );

        add_submenu_page(
            $this->_getConfig()['menu']['root']['menu_slug'],
            $this->_getConfig()['menu']['settings']['page_title'],
            $this->_getConfig()['menu']['settings']['menu_title'],
            $this->_getConfig()['menu']['settings']['capability'],
            $this->_getConfig()['menu']['settings']['menu_slug'],
            function () use ($templateManager) {
                return $this->_renderSettingsPage($templateManager);
            }
        );

        add_submenu_page(
            $this->_getConfig()['menu']['root']['menu_slug'],
            $this->_getConfig()['menu']['about']['page_title'],
            $this->_getConfig()['menu']['about']['menu_title'],
            $this->_getConfig()['menu']['about']['capability'],
            $this->_getConfig()['menu']['about']['menu_slug'],
            function () use ($templateManager) {
                return $this->_renderAboutPage($templateManager);
            }
        );
    }

    /**
     * Render screen options
     *
     * @param TemplateManager $templateManager
     * @return mixed
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
     * @param TemplateManager $templateManager
     * @return string
     * @throws \Exception
     */
    protected function _renderAboutPage($templateManager)
    {
        throw new \Exception('Implement About page.');
    }
}

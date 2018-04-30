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
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;
use Dhii\Util\String\StringableInterface as Stringable;

class WpBookingsUiModule extends AbstractBaseModule
{
    use ContainerGetCapableTrait;

    use CreateContainerExceptionCapableTrait;

    use CreateNotFoundExceptionCapableTrait;

    use NormalizeKeyCapableTrait;

    /**
     * Registered booking's page ID.
     *
     * @var
     */
    public $bookingsPageId;

    /**
     * Page where metabox application should be shown.
     *
     * @var
     */
    public $metaboxPageId;

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
     * @inheritdoc
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(WP_BOOKINGS_UI_MODULE_DIR . '/config.php'),
            [
                'template_manager' => function ($c) {
                    $templateManager = new TemplateManager($this->eventManager, $this->eventFactory);
                    $templateManager->registerTemplates($c->get('templates'));
                    return $templateManager;
                }
            ]);
    }

    /**
     * @inheritdoc
     */
    public function run(ContainerInterface $c = null)
    {
        $this->metaboxPageId = $c->get('metabox/post_type');

        /* @var EventManagerInterface $eventManager */
        $eventManager = $c->get('event_manager');

        /** @var TemplateManager $templateManager */
        $templateManager = $c->get('template_manager');

        $assetsConfig = $this->_getContainerFactory()->make([
            ContainerFactoryInterface::K_DATA => $c->get('assets')
        ]);
        $eventManager->attach('admin_enqueue_scripts', function () use ($assetsConfig, $c) {
            $this->_enqueueAssets($assetsConfig, $c);
        }, 999);

        $eventManager->attach('admin_init', function () use ($eventManager, $templateManager, $c) {
            $this->_adminInit($eventManager, $templateManager, $c);
        });

        $eventManager->attach('admin_menu', function () use ($templateManager, $c) {
            $this->_adminMenu($templateManager, $c);
        });

        $eventManager->attach('wp_ajax_set_' . $c->get('screen_options/key'), function () use ($c) {
            $data = json_decode(file_get_contents('php://input'), true);
            $statuses = $data['statuses'];
            $this->_setScreenStatuses($c, $statuses);
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
            $this->metaboxPageId,
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
     *
     * @param ContainerInterface $c
     * @return array
     */
    protected function _getBookingsAppState($c)
    {
        $endpointsConfig = $c->get('endpointsConfig');
        foreach ($endpointsConfig as $namespace => $endpoints) {
            foreach ($endpoints as $purpose => $endpoint) {
                if ($endpoint['endpoint'][0] !== '/') {
                    continue;
                }
                $endpointsConfig[$namespace][$purpose]['endpoint'] = rest_url($endpoint['endpoint']);
            }
        }

        /* @var ControllerInterface $controller */
        $controller = $c->get('eddbk_services_controller');
        $services = iterator_to_array($controller->get());

        return [
            /*
             * All available statuses in application.
             */
            'statuses' => $this->_trigger('eddbk_bookings_statuses', [
                'statuses' => [
                    "draft" => __("Draft", EDDBK_TEXT_DOMAIN),
                    "in_cart" => __("Cart", EDDBK_TEXT_DOMAIN),
                    "pending" => __("Pending", EDDBK_TEXT_DOMAIN),
                    "approved" => __("Approved", EDDBK_TEXT_DOMAIN),
                    "rejected" => __("Rejected", EDDBK_TEXT_DOMAIN),
                    "scheduled" => __("Scheduled", EDDBK_TEXT_DOMAIN),
                    "cancelled" => __("Cancelled", EDDBK_TEXT_DOMAIN),
                    "completed" => __("Completed", EDDBK_TEXT_DOMAIN)
                ]
            ])->getParam('statuses'),

            /*
             * Statuses that enabled for filtering bookings.
             */
            'screenStatuses' => $this->_trigger('eddbk_bookings_screen_statuses', [
                'screenStatuses' => $this->_getScreenStatuses($c)
            ])->getParam('screenStatuses'),

            /*
             * List of available services.
             */
            'services' => $this->_trigger('eddbk_bookings_services', [
                'services' => $services
            ])->getParam('services'),

            'statusesEndpoint' => $c->get('screen_options/endpoint'),

            'endpointsConfig' => $endpointsConfig
        ];
    }

    /**
     * Save visible screen statuses in per-user options.
     *
     * @param ContainerInterface $c
     * @param array $statuses
     */
    protected function _setScreenStatuses($c, $statuses)
    {
        if (!($user = wp_get_current_user())) {
            wp_die('0');
        }

        update_user_option(
            $user->ID,
            $c->get('screen_options/key'),
            json_encode($statuses)
        );

        wp_die('1');
    }

    /**
     * Return list of all statuses that is available for user by default.
     *
     * @return array|mixed|object
     */
    protected function _getScreenStatuses($c)
    {
        if (!$user = wp_get_current_user())
            return [];

        $screenOptions = get_user_option($c->get('screen_options/key'), $user->ID);
        if (!$screenOptions) {
            return [
                "in_cart", "draft", "pending", "approved", "rejected", "scheduled", "cancelled", "completed"
            ];
        }
        $screenOptions = json_decode($screenOptions);

        return $screenOptions;
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
     * @param ContainerInterface $assetsConfig Assets container config
     * @param ContainerInterface $c Module config
     */
    protected function _enqueueAssets(ContainerInterface $assetsConfig, ContainerInterface $c)
    {
        if (!$this->_onAppPage()) {
            return;
        }

        wp_enqueue_script('rc-app-require', $assetsConfig->get('require'), [], false, true);

        wp_localize_script('rc-app-require', 'RC_APP_REQUIRE_FILES', [
            'app' => $assetsConfig->get('bookings_ui/dist/app.min.js')
        ]);

        /*
         * All application components located here
         */
        wp_enqueue_script('rc-app', $assetsConfig->get('bookings_ui/assets/js/main.js'), [], false, true);
        wp_enqueue_style('rc-app', $assetsConfig->get('bookings_ui/dist/wp-booking-ui.css'));

        foreach ($assetsConfig->get('style_deps') as $styleDep) {
            wp_enqueue_style('rc-app-require', $styleDep);
        }

        $state = $this->_onBookingsPage() ? $this->_getBookingsAppState($c) : $this->_getServiceAppState();

        wp_localize_script('rc-app', 'EDDBK_APP_STATE', $state);
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @param EventManagerInterface $eventManager
     * @param TemplateManager $templateManager
     */
    protected function _adminInit($eventManager, $templateManager, $c)
    {
        /*
         * Add metabox with availabilities configuration to
         * service's edit page.
         */
        add_meta_box(
            $c->get('metabox/id'),
            $c->get('metabox/title'),
            function () use ($templateManager) {
                echo $this->_renderMetabox($templateManager);
            },
            $c->get('metabox/post_type')
        );

        /*
         * Add screen options on bookings management page.
         */
        add_filter('screen_settings', function ($settings, \WP_Screen $screen) use ($templateManager) {
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
     * @param ContainerInterface $c
     */
    protected function _adminMenu($templateManager, $c)
    {
        $this->bookingsPageId = add_menu_page(
            $c->get('menu/root/page_title'),
            $c->get('menu/root/menu_title'),
            $c->get('menu/root/capability'),
            $c->get('menu/root/menu_slug'),
            function () use ($templateManager) {
                echo $this->_renderMainPage($templateManager);
            },
            $c->get('menu/root/icon'),
            $c->get('menu/root/position')
        );

        add_submenu_page(
            $c->get('menu/root/menu_slug'),
            $c->get('menu/settings/page_title'),
            $c->get('menu/settings/menu_title'),
            $c->get('menu/settings/capability'),
            $c->get('menu/settings/menu_slug'),
            function () use ($templateManager) {
                return $this->_renderSettingsPage($templateManager);
            }
        );

        add_submenu_page(
            $c->get('menu/root/menu_slug'),
            $c->get('menu/about/page_title'),
            $c->get('menu/about/menu_title'),
            $c->get('menu/about/capability'),
            $c->get('menu/about/menu_slug'),
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

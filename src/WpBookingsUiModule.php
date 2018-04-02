<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

class WpBookingsUiModule extends AbstractBaseModule
{
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
            ['wp_event_manager'],
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
        $eventManager = $c->get('wp_event_manager');

        /** @var TemplateManager $templateManager */
        $templateManager = $c->get('template_manager');

        $eventManager->attach('admin_enqueue_scripts', function () {
            $this->_enqueueAssets();
        });

        $eventManager->attach('admin_init', function () use ($eventManager, $templateManager) {
            $this->_adminInit($eventManager, $templateManager);
        });

        $eventManager->attach('admin_menu', function () use ($templateManager) {
            $this->_adminMenu($templateManager);
        });
    }

    /**
     * Enqueue all UI assets.
     */
    protected function _enqueueAssets()
    {
        $assetsConfig = $this->_getConfig()['assets'];

        wp_enqueue_script('rc-app-require', $assetsConfig['require'], [], false, true);
        wp_localize_script('rc-app-require', 'RC_APP_REQUIRE_FILES', $assetsConfig['require_assets']);

        /*
         * All application components located here
         */
        wp_enqueue_script('rc-app', $assetsConfig['app'], [], false, true);
        wp_enqueue_style('rc-app', $assetsConfig['style']);

        foreach ($assetsConfig['style_deps'] as $styleDep) {
            wp_enqueue_style('rc-app-require', $styleDep);
        }
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
                return $this->_renderMetabox($templateManager);
            },
            $this->_getConfig()['metabox']['post_type']
        );

        /*
         * Add screen options on bookings management page.
         */
        $eventManager->attach('screen_settings', function($settings, $screen = null) use ($templateManager) {
            if ($this->bookingsPageId !== $screen->base)
                return $settings;

            return $this->_renderBookingsScreenOptions($templateManager);
        });
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

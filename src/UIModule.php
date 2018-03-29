<?php

namespace RebelCode\Bookings\Wordpress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

class UIModule extends AbstractBaseModule
{
    private $bookingsPageId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerFactoryInterface $containerFactory The factory for creating container instances.
     * @throws \Dhii\Exception\InternalException
     */
    public function __construct(ContainerFactoryInterface $containerFactory)
    {
        $this->_initModule(
            $containerFactory,
            'bookings-ui-module',
            ['wp-events'],
            $this->_loadPhpConfigFile(__DIR__ . '/../config.php')
        );
    }

    /**
     * @inheritdoc
     */
    public function setup()
    {
        return $this->_createContainer([
            'template-manager' => function (ContainerInterface $c) {
                $templateManager = new TemplateManager($c);
                $templateManager->registerTemplates();
                return $templateManager;
            }
        ]);
    }

    /**
     * @inheritdoc
     */
    public function run(ContainerInterface $c = null)
    {
        /* @var $eventManager EventManagerInterface */
        $eventManager = $c->get('event-manager');

        $eventManager->attach('admin_init', function () use ($c) {
            $this->_adminInit($c);
        });

        $eventManager->attach('admin_menu', function () use ($c) {
            $this->_adminMenu($c);
        });
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @param ContainerInterface $c
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _adminInit(ContainerInterface $c)
    {
        /* @var $eventManager EventManagerInterface */
        $eventManager = $c->get('event-manager');

        /*
         * Add metabox with availablities configuration to
         * service's edit page.
         */
        add_meta_box(
            $this->_getConfig()['metabox']['id'],
            $this->_getConfig()['metabox']['title'],
            function () use ($c) {
                return $this->_renderMetabox($c);
            },
            $this->_getConfig()['metabox']['post_type']
        );

        /*
         * Add screen options on bookings management page.
         */
        $eventManager->attach('screen_settings', function($settings, \WP_Screen $screen) use ($c) {
            if ($this->bookingsPageId !== $screen->base)
                return $settings;

            return $this->_renderBookingsScreenOptions($c);
        });
    }

    /**
     * Register pages in the admin menu.
     *
     * @param ContainerInterface $c
     */
    protected function _adminMenu(ContainerInterface $c)
    {
        $this->bookingsPageId = add_menu_page(
            $this->_getConfig()['menu']['root']['page_title'],
            $this->_getConfig()['menu']['root']['menu_title'],
            $this->_getConfig()['menu']['root']['capability'],
            $this->_getConfig()['menu']['root']['menu_slug'],
            function () use ($c) {
                return $this->_renderMainPage($c);
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
            function () use ($c) {
                return $this->_renderSettingsPage($c);
            }
        );

        add_submenu_page(
            $this->_getConfig()['menu']['root']['menu_slug'],
            $this->_getConfig()['menu']['about']['page_title'],
            $this->_getConfig()['menu']['about']['menu_title'],
            $this->_getConfig()['menu']['about']['capability'],
            $this->_getConfig()['menu']['about']['menu_slug'],
            function () use ($c) {
                return $this->_renderAboutPage($c);
            }
        );
    }

    /**
     * Render screen options
     *
     * @param ContainerInterface $c
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _renderBookingsScreenOptions(ContainerInterface $c)
    {
        return $c->get('template-manager')->render('booking/screen-options');
    }

    /**
     * Register metabox for service's bookings settings.
     *
     * @param ContainerInterface $c
     * @return string
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _renderMetabox($c)
    {
        return $c->get('template-manager')->render('availability/metabox');
    }

    /**
     * Render main booking view.
     *
     * @param $c
     * @return mixed
     */
    protected function _renderMainPage($c)
    {
        return $c->get('template-manager')->render('booking/bookings-page');
    }

    /**
     * Render settings page.
     *
     * @param $c
     * @return string
     * @throws \Exception
     */
    protected function _renderSettingsPage($c)
    {
        throw new \Exception('Implement Settings page.');
    }

    /**
     * Render about page.
     *
     * @param $c
     * @return string
     * @throws \Exception
     */
    protected function _renderAboutPage($c)
    {
        throw new \Exception('Implement About page.');
    }
}
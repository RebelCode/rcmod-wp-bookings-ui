<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Closure;
use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use stdClass;
use Traversable;

/**
 * Handler registering whole application UI in dashboard.
 *
 * @since [*next-version*]
 */
class RegisterUiHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use RegisterApplicationPagesCapable;

    /* @since [*next-version*] */
    use ReadApplicationPagesCapable;

    /**
     * The list of available menu pages. For more information about page config structure read config doc.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $menuPagesConfig;

    /**
     * Metabox configuration.
     *
     * @since [*next-version*]
     *
     * @var array|MapInterface|stdClass
     */
    protected $metaboxConfig;

    /**
     * RegisterUiHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable  $menuPagesConfig The list of available menu pages. For more information about page config structure read config doc.
     * @param array|stdClass|MapInterface $metaboxConfig   Metabox configuration.
     * @param EventManagerInterface       $eventManager    The event manager.
     * @param EventFactoryInterface       $eventFactory    The event factory.
     */
    public function __construct($menuPagesConfig, $metaboxConfig, $eventManager, $eventFactory)
    {
        $this->menuPagesConfig = $menuPagesConfig;
        $this->metaboxConfig   = $metaboxConfig;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $this->_registerMenu($this->menuPagesConfig);
        $this->_registerMetabox($this->metaboxConfig);

        $this->_attach('eddbk_registered_application_pages', function (EventInterface $event) {
            $event->setParams([
                'pages' => $this->_getApplicationPages(),
            ]);
        });
    }

    /**
     * Register WP menu pages according passed menu configuration.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $menuPagesConfig The list of available menu pages. For more information about page config structure read config doc.
     */
    protected function _registerMenu($menuPagesConfig)
    {
        $menuPagesConfig = $this->_normalizeIterable($menuPagesConfig);

        foreach ($menuPagesConfig as $pageKey => $pageConfig) {
            $pageId = $this->_containerHas($pageConfig, 'root_slug') ? $this->_registerSubmenuPage($pageConfig) : $this->_registerMenuPage($pageConfig);
            $this->_registerApplicationPage($pageKey, $pageId);

            if ($this->_containerHas($pageConfig, 'screen_settings_filter')) {
                $this->_attach(
                    'screen_settings',
                    $this->_getScreenSettingsRendererCallback($pageKey, $this->_containerGet($pageConfig, 'screen_settings_filter'))
                );
            }
        }
    }

    /**
     * Register metabox according passed configuration.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $metaboxConfig Metabox configuration.
     */
    protected function _registerMetabox($metaboxConfig)
    {
        $this->_registerApplicationPage('metabox', $this->_containerGet($metaboxConfig, 'post_type'));

        /*
         * Add metabox with availabilities configuration to
         * service's edit page.
         */
        add_meta_box(
            $this->_containerGet($metaboxConfig, 'id'),
            $this->__($this->_containerGet($metaboxConfig, 'title')),
            $this->_getRendererCallback($metaboxConfig),
            $this->_containerGet($metaboxConfig, 'post_type')
        );
    }

    /**
     * Register menu page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $pageConfig Menu page configuration. Look for description in config.
     *
     * @return string Generated WP page ID.
     */
    protected function _registerMenuPage($pageConfig)
    {
        return add_menu_page(
            $this->__($this->_containerGet($pageConfig, 'page_title')),
            $this->__($this->_containerGet($pageConfig, 'menu_title')),
            $this->_containerGet($pageConfig, 'capability'),
            $this->_containerGet($pageConfig, 'menu_slug'),
            $this->_getRendererCallback($pageConfig),
            $this->_containerGet($pageConfig, 'icon'),
            $this->_containerGet($pageConfig, 'position')
        );
    }
    /**
     * Register submenu page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $pageConfig Submenu page configuration. Look for description in config.
     *
     * @return string Generated WP page ID.
     */
    protected function _registerSubmenuPage($pageConfig)
    {
        return add_submenu_page(
            $this->_containerGet($pageConfig, 'root_slug'),
            $this->__($this->_containerGet($pageConfig, 'page_title')),
            $this->__($this->_containerGet($pageConfig, 'menu_title')),
            $this->_containerGet($pageConfig, 'capability'),
            $this->_containerGet($pageConfig, 'menu_slug'),
            $this->_getRendererCallback($pageConfig)
        );
    }

    /**
     * Get function for rendering page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ContainerInterface $config Configuration of page.
     *
     * @return Closure Function for retrieving rendered content using config's renderer filter.
     */
    protected function _getRendererCallback($config)
    {
        return function () use ($config) {
            echo $this->_trigger($this->_containerGet($config, 'renderer'))->getParam('content');
        };
    }

    /**
     * Get function for rendering screen options.
     *
     * @since [*next-version*]
     *
     * @param string $pageKey Page identifier on which screen options should be rendered.
     * @param string $handler Render filter name.
     *
     * @return Closure Function for rendering content of screen options using render filter.
     */
    protected function _getScreenSettingsRendererCallback($pageKey, $handler)
    {
        return function ($event) use ($pageKey, $handler) {
            if (!$this->_isOnPage('bookings')) {
                return $event->getParam(0);
            }
            $event->setParams([
                $this->_trigger($handler)->getParam('content'),
            ]);
        };
    }
}

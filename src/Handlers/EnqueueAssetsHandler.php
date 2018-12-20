<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use stdClass;

/**
 * Handler for enqueueing all assets (scrips, styles and state) on client.
 *
 * @since [*next-version*]
 */
class EnqueueAssetsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use ReadApplicationPagesCapable;

    /**
     * The name of WP handle to attach require related scripts and styles.
     *
     * @since [*next-version*]
     */
    const REQUIRE_APP_ID = 'rc-app-require';

    /**
     * The name of WP handle to attach app related scripts and styles.
     *
     * @since [*next-version*]
     */
    const APP_ID = 'rc-app';

    /**
     * The name of state variable for outputting state.
     *
     * @since [*next-version*]
     */
    const VAR_STATE_NAME = 'EDDBK_APP_STATE';

    /**
     * Map of existing assets identifiers to their real urls.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $assetsUrlMap;

    /**
     * Map of the readable assets paths to their identifiers.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $assets;

    /**
     * Map of the application page if to the filter name for retrieving state.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $stateFiltersConfig;

    /**
     * EnqueueAssetsHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $assetsUrlMap       Map of existing assets identifiers to their real urls.
     * @param array|stdClass|MapInterface $assets             Map of the readable assets paths to their identifiers.
     * @param array|stdClass|MapInterface $stateFiltersConfig Map of the application page if to the filter name for retrieving state.
     * @param EventManagerInterface       $eventManager       The event manager.
     * @param EventFactoryInterface       $eventFactory       The event factory.
     */
    public function __construct($assetsUrlMap, $assets, $stateFiltersConfig, $eventManager, $eventFactory)
    {
        $this->assetsUrlMap       = $assetsUrlMap;
        $this->assets             = $assets;
        $this->stateFiltersConfig = $stateFiltersConfig;

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

        $this->_setApplicationPages($this->_trigger('eddbk_registered_application_pages')->getParam('pages'));

        if (!$this->_isOnApplicationPage()) {
            return;
        }

        $this->_enqueueScripts($this->assetsUrlMap, $this->assets);
        $this->_enqueueStyles($this->assetsUrlMap, $this->assets);

        $this->_enqueueState($this->stateFiltersConfig);
    }

    /**
     * Enqueue scripts on the page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $assetsUrlMap Map of existing assets identifiers to their real urls.
     * @param array|stdClass|MapInterface $assets       Map of the readable assets paths to their identifiers.
     */
    protected function _enqueueScripts($assetsUrlMap, $assets)
    {
        wp_enqueue_script(self::APP_ID, $this->_containerGet($assetsUrlMap, $this->_containerGet($assets, 'bookings/app.min.js')), [], false, true);
    }

    /**
     * Enqueue styles on the page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $assetsUrlMap Map of existing assets identifiers to their real urls.
     * @param array|stdClass|MapInterface $assets       Map of the readable assets paths to their identifiers.
     */
    protected function _enqueueStyles($assetsUrlMap, $assets)
    {
        foreach ($this->_containerGet($assets, 'styles') as $styleId => $styleDependency) {
            wp_enqueue_style(self::APP_ID . '-' . $styleId, $this->_containerGet($assetsUrlMap, $styleDependency));
        }
    }

    /**
     * Enqueue state for current application page.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $stateFiltersConfig Map of the application page if to the filter name for retrieving state.
     */
    protected function _enqueueState($stateFiltersConfig)
    {
        $state = $this->_trigger($this->_containerGet($stateFiltersConfig, 'general'))->getParams();

        $applicationPage = $this->_getCurrentApplicationPage();
        if ($this->_containerHas($stateFiltersConfig, $applicationPage)) {
            $state = $this->_trigger($this->_containerGet($stateFiltersConfig, $applicationPage), $state)->getParams();
        }

        wp_localize_script(self::APP_ID, self::VAR_STATE_NAME, $state);
    }
}

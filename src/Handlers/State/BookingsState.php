<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\State;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventManagerInterface;
use stdClass;
use Traversable;

/**
 * The main handler for bookings UI state.
 *
 * @since [*next-version*]
 */
class BookingsState extends StateHandler
{
    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /**
     * List of endpoints configuration.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $endpointsConfig;

    /**
     * ServiceState constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $endpointsConfig List of endpoints configuration.
     * @param EventManagerInterface      $eventManager    The event manager.
     * @param EventFactoryInterface      $eventFactory    The event factory.
     */
    public function __construct($endpointsConfig, $eventManager, $eventFactory)
    {
        $this->endpointsConfig = $endpointsConfig;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getState()
    {
        return [
            /*
             * List of available services.
             */
            'services' => $this->_getServices(),

            'endpointsConfig' => $this->_prepareEndpoints($this->endpointsConfig),
        ];
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
     * @param array|stdClass|Traversable $endpointsConfig List of endpoints configuration.
     *
     * @return array Prepared array of endpoints to use in front-end application.
     */
    protected function _prepareEndpoints($endpointsConfig)
    {
        $endpointsConfig = $this->_normalizeIterable($endpointsConfig);

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
}

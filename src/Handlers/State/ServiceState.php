<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\State;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * The main handler for service UI state.
 *
 * @since [*next-version*]
 */
class ServiceState extends StateHandler
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /**
     * ServiceState constructor.
     *
     * @since [*next-version*]
     *
     * @param EventManagerInterface $eventManager The event manager.
     * @param EventFactoryInterface $eventFactory The event factory.
     */
    public function __construct($eventManager, $eventFactory)
    {
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
        $serviceId = $this->_getServiceId();

        return $this->_trigger('eddbk_services_nedit_ui_state', $this->_getDefaultState($serviceId))->getParams();
    }

    /**
     * Get ID of the service.
     *
     * @since [*next-version*]
     *
     * @return int|string
     */
    protected function _getServiceId()
    {
        return get_post()->ID;
    }

    /**
     * Get default (empty) state.
     *
     * @since [*next-version*]
     *
     * @param int|string $serviceId Service id.
     *
     * @return array Default state.
     */
    protected function _getDefaultState($serviceId)
    {
        return [
            'id' => $serviceId,

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
                'allowCustomerChangeTimezone' => false,
            ],
        ];
    }
}

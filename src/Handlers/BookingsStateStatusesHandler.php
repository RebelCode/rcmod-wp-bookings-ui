<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use Psr\EventManager\EventInterface;
use Dhii\Event\EventFactoryInterface;
use stdClass;
use Traversable;

/**
 * Handler for adding statuses information to state on bookings page.
 * It will add list of available statuses, list of visible booking statuses to filter bookings
 * and endpoint for saving list of visible booking statuses.
 *
 * @since [*next-version*]
 */
class BookingsStateStatusesHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /**
     * List of statuses keys available in application.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $statuses;

    /**
     * Map of known statuses keys translations.
     *
     * @since [*next-version*]
     *
     * @var MapInterface
     */
    protected $statusesLabels;

    /**
     * Option key name to save screen statuses config.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screenOptionsKey;

    /**
     * Endpoint for saving statuses.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $statusesEndpoint;

    /**
     * StatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $statuses         List of statuses key in application.
     * @param MapInterface               $statusesLabels   Map of known status keys to statuses labels.
     * @param string                     $screenOptionsKey Option key name to save screen statuses config.
     * @param string                     $statusesEndpoint Endpoint for saving statuses.
     * @param EventManagerInterface      $eventManager     The event manager.
     * @param EventFactoryInterface      $eventFactory     The event factory.
     */
    public function __construct(
        $statuses,
        $statusesLabels,
        $screenOptionsKey,
        $statusesEndpoint,
        $eventManager,
        $eventFactory
    ) {
        $this->statuses       = $statuses;
        $this->statusesLabels = $statusesLabels;

        $this->screenOptionsKey = $screenOptionsKey;
        $this->statusesEndpoint = $statusesEndpoint;

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

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getStatusesParams()
        ));
    }

    /**
     * Get status related parameters to attach on event.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getStatusesParams()
    {
        return [
            /*
             * All available booking statuses in application.
             */
            'statuses' => $this->_getTranslatedStatuses($this->statuses, $this->statusesLabels),

            /*
             * List of booking statuses in screen options section that is checked for filtering bookings.
             */
            'screenStatuses' => $this->_getScreenStatuses($this->screenOptionsKey, $this->statuses),

            /*
             * Endpoint for saving booking statuses that will be selected as default for filtering bookings.
             */
            'statusesEndpoint' => $this->statusesEndpoint,
        ];
    }

    /**
     * Get all translated statuses.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $statuses       List of statuses
     * @param MapInterface               $statusesLabels Map of statuses keys to status labels
     *
     * @return array Map of statuses codes and translations.
     */
    protected function _getTranslatedStatuses($statuses, $statusesLabels)
    {
        $statuses = $this->_normalizeArray($statuses);

        $translatedStatuses = [];

        $statuses = $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $statuses,
        ])->getParam('statuses');

        foreach ($statuses as $status) {
            $statusLabel                 = $this->_containerHas($statusesLabels, $status) ? $this->_containerGet($statusesLabels, $status) : $status;
            $translatedStatuses[$status] = $this->__($statusLabel);
        }

        return $translatedStatuses;
    }

    /**
     * Return list of all statuses that will be shown for user by default.
     *
     * @since [*next-version*]
     *
     * @param string                     $key             Screen statuses option key.
     * @param array|Traversable|stdClass $defaultStatuses List of booking statuses selected by default
     *
     * @return string[] List of statuses that user selected to show by default
     */
    protected function _getScreenStatuses($key, $defaultStatuses = [])
    {
        if (!$user = wp_get_current_user()) {
            return [];
        }

        $screenOptions = get_user_option($key, $user->ID);
        if (!$screenOptions) {
            return $this->_normalizeArray($defaultStatuses);
        }
        $screenOptions = json_decode($screenOptions);

        $statuses = $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $screenOptions,
        ])->getParam('statuses');

        return $statuses;
    }
}
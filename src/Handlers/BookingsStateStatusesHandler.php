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
use Psr\Container\ContainerInterface;
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
     * @var array|stdClass|MapInterface
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
     * @param array|Traversable|stdClass  $statuses         List of statuses key in application.
     * @param array|stdClass|MapInterface $statusesLabels   Map of known status keys to statuses labels.
     * @param string                      $screenOptionsKey Option key name to save screen statuses config.
     * @param string                      $statusesEndpoint Endpoint for saving statuses.
     * @param EventManagerInterface       $eventManager     The event manager.
     * @param EventFactoryInterface       $eventFactory     The event factory.
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

        if (!$user = wp_get_current_user()) {
            return;
        }

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getStatusesParams($user->ID)
        ));
    }

    /**
     * Get status related parameters to attach on event.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     *
     * @return array
     */
    protected function _getStatusesParams($userId)
    {
        return [
            /*
             * All available booking statuses in application.
             */
            'statuses' => $this->_getTranslatedStatuses($this->statuses, $this->statusesLabels),

            /*
             * List of booking statuses in screen options section that is checked for filtering bookings.
             */
            'screenStatuses' => $this->_getScreenStatuses($userId, $this->statuses),

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
     * @param array|Traversable|stdClass        $statuses       List of statuses
     * @param array|ContainerInterface|stdClass $statusesLabels Map of statuses keys to status labels
     *
     * @return array Map of statuses codes and translations.
     */
    protected function _getTranslatedStatuses($statuses, $statusesLabels)
    {
        $translatedStatuses = [];

        $statuses = $this->_getVisibleStatuses($statuses);

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
     * @param int                        $userId          User identifier.
     * @param array|Traversable|stdClass $defaultStatuses List of booking statuses selected by default
     *
     * @return string[] List of statuses that user selected to show by default
     */
    protected function _getScreenStatuses($userId, $defaultStatuses = [])
    {
        $screenOptions = $this->_getScreenOptions($userId) ?: $defaultStatuses;

        return $this->_getVisibleStatuses($screenOptions);
    }

    /**
     * Get screen options for given user.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     *
     * @return array|stdClass|null
     */
    protected function _getScreenOptions($userId)
    {
        $screenOptions = get_user_option($this->screenOptionsKey, $userId);
        if (!$screenOptions) {
            return;
        }

        return json_decode($screenOptions);
    }

    /**
     * Get list of statuses keys that should be visible in UI.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $statuses List of statuses key to filter.
     *
     * @return string[] List of statuses keys without hidden ones.
     */
    protected function _getVisibleStatuses($statuses)
    {
        return $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $statuses,
        ])->getParam('statuses');
    }
}

<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;
use Psr\EventManager\EventInterface;
use Dhii\Event\EventFactoryInterface;
use Traversable;

/**
 * Handler for providing the status related state and functionality.
 *
 * @since [*next-version*]
 */
class StatusesHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /**
     * List of statuses keys available in application.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $statuses;

    /**
     * List of statuses keys that are not visible in the UI.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $hiddenStatuses;

    /**
     * Map of known statuses keys translations.
     *
     * @since [*next-version*]
     *
     * @var array
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
     * @param Traversable           $statuses         List of statuses key in application.
     * @param Traversable           $hiddenStatuses   List of hidden statuses in application.
     * @param Traversable           $statusesLabels   Map of known statuses labels.
     * @param string                $screenOptionsKey Option key name to save screen statuses config.
     * @param string                $statusesEndpoint Endpoint for saving statuses.
     * @param EventManagerInterface $eventManager     The event manager.
     * @param EventFactoryInterface $eventFactory     The event factory.
     */
    public function __construct(
        Traversable $statuses,
        Traversable $hiddenStatuses,
        Traversable $statusesLabels,
        $screenOptionsKey,
        $statusesEndpoint,
        $eventManager,
        $eventFactory
    ) {
        $this->statuses       = $this->_normalizeArray($statuses);
        $this->hiddenStatuses = $this->_normalizeArray($hiddenStatuses);
        $this->statusesLabels = $this->_normalizeArray($statusesLabels);

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
     * Attach events for status handling.
     *
     * @since [*next-version*]
     */
    public function run()
    {
        $this->_attach('eddbk_bookings_visible_statuses', function ($event) {
            $event->setParams([
                'statuses' => $this->_getVisibleStatuses($this->statuses, $this->hiddenStatuses),
            ]);
        });

        /*
         * Attach status endpoint.
         */
        $this->_attach('wp_ajax_set_' . $this->screenOptionsKey, function () {
            $data = json_decode(file_get_contents('php://input'), true);
            $statuses = $data['statuses'];
            $this->_setScreenStatuses($this->screenOptionsKey, $statuses);
        });
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
             * All available statuses in application.
             */
            'statuses' => $this->_getTranslatedStatuses($this->statuses, $this->statusesLabels),

            /*
             * Statuses that enabled for filtering bookings.
             */
            'screenStatuses' => $this->_getScreenStatuses($this->screenOptionsKey, $this->statuses),

            /*
             * Endpoint for saving statuses
             */
            'statusesEndpoint' => $this->statusesEndpoint,
        ];
    }

    /**
     * Get array of visible statuses for UI.
     *
     * @since [*next-version*]
     *
     * @param array $statuses       List of all statuses.
     * @param array $hiddenStatuses List of statuses that shouldn't be shown.
     *
     * @return array Resulting array of statuses that should be visible in the UI.
     */
    protected function _getVisibleStatuses($statuses, $hiddenStatuses)
    {
        return array_values(array_filter($statuses, function ($status) use ($hiddenStatuses) {
            return !in_array($status, $hiddenStatuses);
        }));
    }

    /**
     * Get all translated statuses.
     *
     * @since [*next-version*]
     *
     * @param array $statuses       List of statuses
     * @param array $statusesLabels Map of statuses and it's labels
     *
     * @return array Map of statuses codes and translations.
     */
    protected function _getTranslatedStatuses($statuses, $statusesLabels)
    {
        $translatedStatuses = [];

        $statuses = $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $statuses,
        ])->getParam('statuses');

        foreach ($statuses as $status) {
            $statusLabel                 = array_key_exists($status, $statusesLabels) ? $statusesLabels[$status] : $status;
            $translatedStatuses[$status] = $this->__($statusLabel);
        }

        return $translatedStatuses;
    }

    /**
     * Save visible screen statuses in per-user options.
     *
     * @since [*next-version*]
     *
     * @param string   $key      Key of option where statuses stored.
     * @param string[] $statuses List of statuses to save.
     */
    protected function _setScreenStatuses($key, $statuses)
    {
        if (!($user = wp_get_current_user())) {
            wp_die('0');
        }

        update_user_option(
            $user->ID,
            $key,
            json_encode($statuses)
        );

        wp_die('1');
    }

    /**
     * Return list of all statuses that will be shown for user by default.
     *
     * @since [*next-version*]
     *
     * @param string   $key             Screen statuses option key.
     * @param string[] $defaultStatuses Array of statuses selected by default
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
            return $defaultStatuses;
        }
        $screenOptions = json_decode($screenOptions);

        $statuses = $this->_trigger('eddbk_bookings_visible_statuses', [
            'statuses' => $screenOptions,
        ])->getParam('statuses');

        return $statuses;
    }
}

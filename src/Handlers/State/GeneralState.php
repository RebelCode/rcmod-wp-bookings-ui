<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\State;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Bookings\WordPress\Module\Handlers\GetVisibleStatusesCapable;
use RebelCode\Bookings\WordPress\Module\IteratorToArrayRecursiveCapableTrait;
use RebelCode\Modular\Events\EventsConsumerTrait;
use stdClass;
use Traversable;

/**
 * Handler for UI state config. In the config we pass data needed by UI application to
 * work correctly. Such as site timezone and formats configuration.
 *
 * @since [*next-version*]
 */
class GeneralState extends StateHandler
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use GetVisibleStatusesCapable;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use IteratorToArrayRecursiveCapableTrait;

    /**
     * Settings container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $settingsContainer;

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
     * UI configuration.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $uiConfig;

    /**
     * List of endpoints configuration.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $endpointsConfig;

    /*
     * The WP Rest nonce.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $wpRestNonce;

    /**
     * GeneralUiStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface          $settingsContainer Settings container.
     * @param array|Traversable|stdClass  $statuses          List of statuses key in application.
     * @param array|stdClass|MapInterface $statusesLabels    Map of known status keys to statuses labels.
     * @param array|Traversable|stdClass  $uiConfig          UI config of application.
     * @param array|stdClass|Traversable  $endpointsConfig   List of endpoints configuration.
     * @param string|Stringable           $wpRestNonce       The WP Rest nonce.
     * @param EventManagerInterface       $eventManager      The event manager.
     * @param EventFactoryInterface       $eventFactory      The event factory.
     */
    public function __construct(
        $settingsContainer,
        $statuses,
        $statusesLabels,
        $uiConfig,
        $endpointsConfig,
        $wpRestNonce,
        $eventManager,
        $eventFactory
    ) {
        $this->settingsContainer = $settingsContainer;

        $this->statuses       = $statuses;
        $this->statusesLabels = $statusesLabels;

        $this->uiConfig        = $uiConfig;
        $this->endpointsConfig = $endpointsConfig;

        $this->wpRestNonce = $wpRestNonce;

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
             * Get wp rest nonce string.
             */
            'wp_rest_nonce' => $this->_normalizeString($this->wpRestNonce),

            /*
             * All available booking statuses in application.
             */
            'statuses' => $this->_getTranslatedStatuses($this->statuses, $this->statusesLabels),

            'endpointsConfig' => $this->_prepareEndpoints($this->endpointsConfig),

            'config' => $this->_prepareUiConfig($this->uiConfig),
        ];
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

    /**
     * Prepare UI config for client application.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $uiConfig UI config of application.
     *
     * @return array UI configuration.
     */
    protected function _prepareUiConfig($uiConfig)
    {
        return [
            'timezone'   => $this->_getWebsiteTimezone(),
            'currency'   => $this->_prepareCurrencyConfig($this->_containerGet($uiConfig, 'currency')),
            'formats'    => $this->_prepareFormatsConfig($this->_containerGet($uiConfig, 'formats')),
            'links'      => $this->_prepareLinksConfig($this->_containerGet($uiConfig, 'links')),
            'uiActions'  => $this->_iteratorToArrayRecursive($this->_containerGet($uiConfig, 'ui_actions')),
            'validators' => $this->_iteratorToArrayRecursive($this->_containerGet($uiConfig, 'validators')),

            'weekStartsOn'          => $this->settingsContainer->get('week_starts_on'),
            'defaultCalendarView'   => $this->settingsContainer->get('default_calendar_view'),
            'bookingStatusesColors' => $this->settingsContainer->get('booking_statuses_colors'),
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
     * Get website timezone.
     *
     * @since [*next-version*]
     *
     * @return string Timezone in `America/Indianapolis` form.
     */
    protected function _getWebsiteTimezone()
    {
        $currentOffset = get_option('gmt_offset');
        $tzstring      = get_option('timezone_string');

        // Remove old Etc mappings. Fallback to gmt_offset.
        if (false !== strpos($tzstring, 'Etc/GMT')) {
            $tzstring = '';
        }

        if (empty($tzstring)) {
            if (0 == $currentOffset) {
                $tzstring = 'UTC+0';
            } elseif ($currentOffset < 0) {
                $tzstring = 'UTC' . $currentOffset;
            } else {
                $tzstring = 'UTC+' . $currentOffset;
            }
        }

        return $tzstring;
    }

    /**
     * Prepare application's currency configuration for state.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $currencyConfig Currency config of application.
     *
     * @return array Application currency config.
     */
    protected function _prepareCurrencyConfig($currencyConfig)
    {
        return $this->_normalizeArray($currencyConfig);
    }

    /**
     * Prepare formats config for using in the UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     *
     * @return array Prepared data formats configuration.
     */
    protected function _prepareFormatsConfig($formatsConfig)
    {
        $preparedFormatsConfig = [];
        foreach ($formatsConfig as $key => $config) {
            $preparedFormatsConfig[$key] = $this->_normalizeArray($config);
        }

        return $preparedFormatsConfig;
    }

    /**
     * Prepare list of links to booking related entities for using in the UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $linksConfig List of links to booking related entities (clients, services).
     *
     * @return array Prepared links list.
     */
    protected function _prepareLinksConfig($linksConfig)
    {
        $preparedLinksConfig = [];
        foreach ($linksConfig as $key => $link) {
            $preparedLinksConfig[$key] = admin_url($link);
        }

        return $preparedLinksConfig;
    }
}

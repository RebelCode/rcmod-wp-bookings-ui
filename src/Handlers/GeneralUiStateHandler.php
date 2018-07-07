<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Invocation\InvocableInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for UI state config. In the config we pass data needed by UI application to
 * work correctly. Such as site timezone and formats configuration.
 *
 * @since [*next-version*]
 */
class GeneralUiStateHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Currency config of application.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $currencyConfig;

    /**
     * Configuration of application formats to format some data.
     * For example, datetime formats.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $formatsConfig;

    /**
     * List of links to booking related entities (clients, services).
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $linksConfig;

    /**
     * GeneralUiStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $currencyConfig Currency config of application.
     * @param array|Traversable|stdClass $formatsConfig  List of available data formats in application.
     * @param array|Traversable|stdClass $linksConfig    List of links to booking related entities (clients, services).
     */
    public function __construct($currencyConfig, $formatsConfig, $linksConfig)
    {
        $this->currencyConfig = $currencyConfig;
        $this->formatsConfig  = $formatsConfig;
        $this->linksConfig    = $linksConfig;
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

        $event->setParams([
            'config' => $this->_getUiConfig($this->currencyConfig, $this->formatsConfig, $this->linksConfig),
        ]);
    }

    /**
     * Get config for UI application.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $currencyConfig Currency config of application.
     * @param array|Traversable|stdClass $formatsConfig  List of available data formats in application.
     * @param array|Traversable|stdClass $linksConfig    List of links to booking related entities (clients, services).
     *
     * @return array UI configuration.
     */
    protected function _getUiConfig($currencyConfig, $formatsConfig, $linksConfig)
    {
        return [
            'timezone' => $this->_getWebsiteTimezone(),
            'currency' => $this->_prepareCurrencyConfig($currencyConfig),
            'formats'  => $this->_prepareFormatsConfig($formatsConfig),
            'links'    => $this->_prepareLinksConfig($linksConfig),
        ];
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

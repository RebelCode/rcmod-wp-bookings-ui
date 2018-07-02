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
     * Configuration of application formats to format some data.
     * For example, datetime formats.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $formatsConfig;

    /**
     *  List of links to booking related entities (clients, services).
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
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     * @param array|Traversable|stdClass $linksConfig   List of links to booking related entities (clients, services).
     */
    public function __construct($formatsConfig, $linksConfig)
    {
        $this->formatsConfig = $formatsConfig;
        $this->linksConfig   = $linksConfig;
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
            'config' => $this->_getUiConfig($this->formatsConfig, $this->linksConfig),
        ]);
    }

    /**
     * Get config for UI application.
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     * @param array|Traversable|stdClass $linksConfig   List of links to booking related entities (clients, services).
     *
     * @return array UI configuration.
     */
    protected function _getUiConfig($formatsConfig, $linksConfig)
    {
        return [
            'timezone' => $this->_getWebsiteTimezone(),
            'currency' => $this->_getWebsiteCurrency(),
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
     * Get currency for website.
     *
     * @since [*next-version*]
     *
     * @return array Website currency information.
     */
    protected function _getWebsiteCurrency()
    {
        return [
            'name'   => edd_get_currency(),
            'symbol' => edd_currency_symbol(),
        ];
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

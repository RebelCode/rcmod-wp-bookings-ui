<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\State;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Handler for settings UI state.
 *
 * @since [*next-version*]
 */
class SettingsState extends StateHandler
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

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
     * Settings container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $settingsContainer;

    /**
     * Map of available fields to their available options.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $fieldsOptions;

    /**
     * List of settings fields.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $fields;

    /**
     * Update endpoint configuration.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $updateEndpoint;

    /**
     * SettingsStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface          $settingsContainer Settings container.
     * @param array|stdClass|MapInterface $fieldsOptions     Map of available fields to their available options.
     * @param array|stdClass|Traversable  $fields            List of settings fields.
     * @param array|stdClass|MapInterface $updateEndpoint    Configuration of update endpoint.
     */
    public function __construct($settingsContainer, $fieldsOptions, $fields, $updateEndpoint)
    {
        $this->settingsContainer = $settingsContainer;
        $this->fieldsOptions     = $fieldsOptions;
        $this->fields            = $fields;
        $this->updateEndpoint    = $updateEndpoint;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getState()
    {
        return [
            'settingsUi' => [
                'preview'            => $this->_getPreviewSettingsFields(),
                'options'            => $this->_prepareFieldsOptions($this->fieldsOptions),
                'values'             => $this->_prepareSettingsValues(),
                'updateEndpoint'     => $this->_normalizeArray($this->updateEndpoint),
                'generalSettingsUrl' => $this->_getGeneralSettingsUrl(),
            ],
        ];
    }

    /**
     * Get website general settings URL.
     *
     * @since [*next-version*]
     *
     * @return string General settings URL.
     */
    protected function _getGeneralSettingsUrl()
    {
        return admin_url('options-general.php');
    }

    /**
     * List of data that can be only previewed. Changing is happening on separate page.
     *
     * @since [*next-version*]
     *
     * @return array Preview fields.
     */
    protected function _getPreviewSettingsFields()
    {
        return [
            'datetimeFormats' => $this->_getWebsiteFormatsPreview(),
        ];
    }

    /**
     * Get website date and time preview.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable Website date and time preview.
     */
    protected function _getWebsiteFormatsPreview()
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        return date($dateFormat) . ' | ' . date($timeFormat);
    }

    /**
     * Prepare fields options for displaying in UI state.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $fieldsOptions Map of available fields to their available options.
     *
     * @return array Prepared fields options for displaying in UI state.
     */
    protected function _prepareFieldsOptions($fieldsOptions)
    {
        $preparedFieldsOptions = [];
        foreach ($fieldsOptions as $optionsKey => $values) {
            $prepared = [];
            foreach ($values as $key => $value) {
                $prepared[$key] = $this->__($value);
            }
            $preparedFieldsOptions[$optionsKey] = $prepared;
        }

        return $preparedFieldsOptions;
    }

    /**
     * Prepare settings values for displaying in UI state.
     *
     * @since [*next-version*]
     *
     * @return array Prepared settings values for displaying in UI state.
     */
    protected function _prepareSettingsValues()
    {
        $values = [];
        foreach ($this->fields as $field) {
            $values[$field] = $this->_getSettingValue($field);
        }

        return $values;
    }

    /**
     * Get setting value of field.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Field name.
     *
     * @return array|mixed Setting value.
     */
    protected function _getSettingValue($field)
    {
        $field = $this->_normalizeString($field);

        return $this->settingsContainer->get($field);
    }
}

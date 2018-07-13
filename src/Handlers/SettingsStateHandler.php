<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for settings UI state.
 *
 * @since [*next-version*]
 */
class SettingsStateHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

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
     * Map of settings keys to their default values.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $settingsValues;

    /**
     * List of settings fields that should be serialized.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $arrayFields;

    /**
     * Setting option prefix.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $prefix;

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
     * @param array|stdClass|MapInterface $fieldsOptions  Map of available fields to their available options.
     * @param array|stdClass|Traversable  $fields         List of settings fields.
     * @param array|stdClass|MapInterface $settingsValues Map of settings keys to their default values.
     * @param array|stdClass|Traversable  $arrayFields    List of settings fields that should be serialized.
     * @param string|Stringable           $prefix         Setting option prefix.
     * @param array|stdClass|MapInterface $updateEndpoint Configuration of update endpoint.
     */
    public function __construct($fieldsOptions, $fields, $settingsValues, $arrayFields, $prefix, $updateEndpoint)
    {
        $this->fieldsOptions  = $fieldsOptions;
        $this->fields         = $fields;
        $this->settingsValues = $settingsValues;
        $this->arrayFields    = $this->_normalizeArray($arrayFields);
        $this->prefix         = $prefix;
        $this->updateEndpoint = $updateEndpoint;
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
            'settingsUi' => [
                'preview'        => $this->_getPreviewSettingsFields(),
                'options'        => $this->_prepareFieldsOptions($this->fieldsOptions),
                'values'         => $this->_prepareSettingsValues(),
                'updateEndpoint' => $this->_normalizeArray($this->updateEndpoint),
            ],
        ]);
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
     * Prepare setting option key from field name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Field name to prepare key from.
     *
     * @return string Prepared setting option key from field name.
     */
    protected function _getFieldKey($field)
    {
        $field = $this->_normalizeString($field);

        return $this->prefix . '/' . $field;
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
        $key   = $this->_getFieldKey($field);
        $value = get_option($key) ?: $this->settingsValues->get($key);

        if (in_array($field, $this->arrayFields)) {
            $value = $this->_normalizeArray($value);
        }

        return $value;
    }
}

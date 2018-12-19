<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Class WpBookingsUiModule.
 *
 * Responsible for providing UI in the dashboard.
 *
 * @since [*next-version*]
 */
class WpBookingsUiModule extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /**
     * Helper class to render templates using events mechanism.
     *
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * Registered booking's page ID.
     *
     * @var string
     */
    protected $bookingsPageId;

    /**
     * Registered service's page ID.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $servicesPageId;

    /**
     * Registered staff's page ID.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $staffMembersPageId;

    /**
     * Page where settings application should be shown.
     *
     * @var string
     */
    protected $settingsPageId;

    /**
     * About page.
     *
     * @var string
     */
    protected $aboutPageId;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module  dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(WP_BOOKINGS_UI_MODULE_CONFIG_FILE),
            $this->_getServicesDefinitions()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->_attach('admin_menu', $c->get('eddbk_register_ui_handler'));

        $this->_attach('admin_enqueue_scripts', $c->get('eddbk_enqueue_assets_handler'), 999);

        /*
         * Rendering handlers
         */
        $this->_attach('eddbk_metabox_render', $c->get('eddbk_metabox_render_handler'));

        $this->_attach('eddbk_bookings_render', $c->get('eddbk_bookings_render_handler'));

        $this->_attach('eddbk_screen_options_render', $c->get('eddbk_screen_options_render_handler'));

        $this->_attach('eddbk_settings_render', $c->get('eddbk_settings_render_handler'));

        $this->_attach('eddbk_about_render', $c->get('eddbk_about_render_handler'));

        /*
         * State handlers
         */
        $this->_attach('eddbk_bookings_ui_state', $c->get('eddbk_bookings_state_handler'));

        $this->_attach('eddbk_bookings_ui_state', $c->get('eddbk_bookings_statuses_state_handler'));

        $this->_attach('eddbk_bookings_ui_state', $c->get('eddbk_bookings_transitions_state_handler'));

        $this->_attach('eddbk_service_ui_state', $c->get('eddbk_service_state_handler'));

        $this->_attach('eddbk_general_ui_state', $c->get('eddbk_general_state_handler'));

        $this->_attach('eddbk_settings_ui_state', $c->get('eddbk_settings_state_handler'));

        /*
         * Other handlers
         */
        $this->_attach('eddbk_bookings_visible_statuses', $c->get('eddbk_bookings_visible_statuses_handler'));

        $this->_attach('wp_ajax_set_' . $c->get('wp_bookings_ui/screen_options/key'), $c->get('eddbk_bookings_save_screen_options_handler'));

        $this->_attach('wp_ajax_' . $c->get('wp_bookings_ui/settings/action'), $c->get('eddbk_bookings_update_settings_handler'));
    }

    /**
     * Get services definitions.
     *
     * @since [*next-version*]
     *
     * @return array Services definitions.
     */
    protected function _getServicesDefinitions()
    {
        $definitions = require_once WP_BOOKINGS_UI_MODULE_DEFINITIONS_PATH;

        return $definitions($this->eventManager, $this->eventFactory, $this->_getContainerFactory());
    }
}

<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\WpBookingsUiModule;

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_KEY', 'wp_bookings_ui');

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_SCREEN_STATUSES_KEY', 'eddbk_screen_options');

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_DIR', __DIR__);

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_DEFINITIONS_PATH', __DIR__ . '/services.php');

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_RELATIVE_DIR', 'modules/rcmod-wp-bookings-ui');

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_CONFIG_DIR', __DIR__ . '/config');

/** @since [*next-version*] */
define('WP_BOOKINGS_UI_MODULE_CONFIG_FILE', WP_BOOKINGS_UI_MODULE_CONFIG_DIR . '/config.php');

/** @since [*next-version*] */
return function (ContainerInterface $c) {
    return new WpBookingsUiModule(
        WP_BOOKINGS_UI_MODULE_KEY,
        [],
        $c->get('config_factory'),
        $c->get('container_factory'),
        $c->get('composite_container_factory'),
        $c->get('event_manager'),
        $c->get('event_factory')
    );
};
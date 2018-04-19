<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\WpBookingsUiModule;

define('WP_BOOKINGS_UI_MODULE_KEY', 'wp_bookings_ui');
define('WP_BOOKINGS_UI_MODULE_DIR', __DIR__);
define('WP_BOOKINGS_UI_MODULE_RELATIVE_DIR', 'modules/rcmod-wp-bookings-ui');

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
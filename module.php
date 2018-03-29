<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\WpBookingsUiModule;

define('WP_BOOKINGS_UI_MODULE_DIR', __DIR__);

return function (ContainerInterface $c) {
    return new WpBookingsUiModule($c->get('container_factory'));
};
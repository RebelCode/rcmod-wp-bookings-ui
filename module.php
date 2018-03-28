<?php

use Psr\Container\ContainerInterface;
use RebelCode\Bookings\WordPress\Module\UIModule;

return function (ContainerInterface $c) {
    return new UIModule($c->get('container-factory'));
};
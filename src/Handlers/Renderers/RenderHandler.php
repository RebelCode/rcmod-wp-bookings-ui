<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

use Dhii\Invocation\InvocableInterface;
use Psr\EventManager\EventInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * Class RenderHandler is event based handler, so it can be attached and hooked later.
 *
 * @since [*next-version*]
 */
abstract class RenderHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

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
            'content' => $this->_render(),
        ]);
    }

    /**
     * Render content.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    abstract protected function _render();
}

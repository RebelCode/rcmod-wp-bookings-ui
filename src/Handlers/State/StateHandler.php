<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\State;

use Dhii\Invocation\InvocableInterface;
use Psr\EventManager\EventInterface;
use RebelCode\Modular\Events\EventsConsumerTrait;

/**
 * Class StateHandler.
 *
 * @since [*next-version*]
 */
abstract class StateHandler implements InvocableInterface
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

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getState()
        ));
    }

    /**
     * Get state of this handler. This state will be merged with already existing state,
     * so state handlers can be called on the same context and they will complete existing state.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    abstract protected function _getState();
}

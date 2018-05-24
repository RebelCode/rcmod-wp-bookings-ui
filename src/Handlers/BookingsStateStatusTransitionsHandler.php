<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handler for adding transitions information to state on bookings page.
 * It will add map of available statuses transitions and transitions translations.
 *
 * @since [*next-version*]
 */
class BookingsStateStatusTransitionsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Map of transitions that should be applied when changing status.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $statusTransitions;

    /**
     * Map of transition keys to labels for transitions.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $transitionsLabels;

    /**
     * StatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $statusTransitions Map of transitions for changing statuses.
     * @param array|stdClass|MapInterface $transitionsLabels Map of transition keys to labels for transitions.
     */
    public function __construct(
        $statusTransitions,
        $transitionsLabels
    ) {
        $this->statusTransitions = $statusTransitions;
        $this->transitionsLabels = $transitionsLabels;
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

        $event->setParams(array_merge(
            $event->getParams(),
            $this->_getTransitionParams()
        ));
    }

    /**
     * Get transitions related parameters to attach on event.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getTransitionParams()
    {
        return [
            /*
             * Transitions for changing booking status
             */
            'statusTransitions' => $this->_prepareStatusTransitions($this->statusTransitions),

            /*
             * Map of transitions keys to transition labels
             */
            'transitionsLabels' => $this->_prepareTransitionsLabels($this->transitionsLabels),
        ];
    }

    /**
     * Get map of status transitions.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $statusTransitions Map of status transitions.
     *
     * @return array Prepared status transitions to be rendered in UI.
     */
    protected function _prepareStatusTransitions($statusTransitions)
    {
        $resultingTransitions = [];
        foreach ($statusTransitions as $status => $transitionsMap) {
            $resultingTransitions[$status] = $this->_normalizeArray($transitionsMap);
        }

        return $resultingTransitions;
    }

    /**
     * Retrieve map of translated transitions labels.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $transitionsLabels Labels of available transitions.
     *
     * @return array Map of translated labels for transitions.
     */
    protected function _prepareTransitionsLabels($transitionsLabels)
    {
        $translatedLabels = [];
        foreach ($transitionsLabels as $transitionKey => $transitionLabel) {
            $translatedLabels[$transitionKey] = $this->__($transitionLabel);
        }

        return $translatedLabels;
    }
}

<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use Traversable;

/**
 * Handler for providing the status related state and functionality.
 *
 * @since [*next-version*]
 */
class StatusTransitionsHandler implements InvocableInterface
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
     * @var array
     */
    protected $statusTransitions;

    /**
     * List of labels for transitions.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $transitionsLabels;

    /**
     * StatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param Traversable $statusTransitions Map of transitions for changing statuses.
     * @param Traversable $transitionsLabels Map of transitions for changing statuses.
     */
    public function __construct(
        Traversable $statusTransitions,
        Traversable $transitionsLabels
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
             * Map of transitions labels
             */
            'transitionsLabels' => $this->_prepareTransitionsLabels($this->transitionsLabels),
        ];
    }

    /**
     * Get map of status transitions.
     *
     * @since [*next-version*]
     *
     * @param Traversable $statusTransitions Map of status transitions.
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
     * @param Traversable $transitionsLabels Labels of available transitions.
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

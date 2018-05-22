<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;

/**
 * Handle saving list of booking statuses that is visible for user by default.
 *
 * @since [*next-version*]
 */
class SaveScreenStatusesHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Option key name to save screen statuses config.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $screenOptionsKey;

    /**
     * SaveScreenStatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param string $screenOptionsKey Option key name to save screen statuses config.
     */
    public function __construct($screenOptionsKey)
    {
        $this->screenOptionsKey = $screenOptionsKey;
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

        $this->_handle();
    }

    /**
     * Save visible bookings statuses for user.
     *
     * @since [*next-version*]
     */
    protected function _handle()
    {
        $data     = json_decode(file_get_contents('php://input'), true);
        $statuses = $data['statuses'];
        $this->_setScreenStatuses($this->screenOptionsKey, $statuses);
    }

    /**
     * Save selected booking statuses in per-user options. It will be used
     * to filter bookings by them and located under screen options section
     * in UI.
     *
     * @since [*next-version*]
     *
     * @param string   $key      Key of option where statuses stored.
     * @param string[] $statuses List of statuses to save.
     */
    protected function _setScreenStatuses($key, $statuses)
    {
        if (!($user = wp_get_current_user())) {
            wp_die('0');
        }

        update_user_option(
            $user->ID,
            $key,
            json_encode($statuses)
        );

        wp_die('1');
    }
}

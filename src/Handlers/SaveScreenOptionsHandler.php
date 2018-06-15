<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Cache\SimpleCacheInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Psr\EventManager\EventInterface;
use stdClass;
use Traversable;

/**
 * Handle saving screen options for user.
 *
 * @since [*next-version*]
 */
class SaveScreenOptionsHandler implements InvocableInterface
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
     * List of screen options field for user.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $screenOptionsFields;

    /**
     * The screen options cache.
     *
     * @since [*next-version*]
     *
     * @var SimpleCacheInterface
     */
    protected $screenOptionsCache;

    /**
     * SaveScreenStatusesHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param string                     $screenOptionsKey    Option key name to save screen statuses config.
     * @param array|stdClass|Traversable $screenOptionsFields List of fields in screen options.
     * @param SimpleCacheInterface       $screenOptionsCache  The screen options cache.
     */
    public function __construct($screenOptionsKey, $screenOptionsFields, $screenOptionsCache)
    {
        $this->screenOptionsKey    = $screenOptionsKey;
        $this->screenOptionsFields = $screenOptionsFields;
        $this->screenOptionsCache  = $screenOptionsCache;
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

        if (!$user = wp_get_current_user()) {
            return;
        }

        $this->_handle($user->ID);
    }

    /**
     * Save screen options for user.
     *
     * @since [*next-version*]
     *
     * @param int $userId User identifier.
     */
    protected function _handle($userId)
    {
        $data    = json_decode(file_get_contents('php://input'), true);
        $options = [];
        foreach ($this->screenOptionsFields as $key) {
            if (array_key_exists($key, $data)) {
                $options[$key] = $data[$key];
            }
        }
        $this->_setScreenOptions($userId, $options);
    }

    /**
     * Save screen options in per-user options.
     *
     * @since [*next-version*]
     *
     * @param int            $userId  User identifier.
     * @param array|stdClass $options Screen options to save.
     */
    protected function _setScreenOptions($userId, $options)
    {
        $key = $this->screenOptionsKey;

        update_user_option(
            $userId,
            $key,
            json_encode($options)
        );

        $this->screenOptionsCache->clear();

        wp_die('1');
    }
}

<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Util\String\StringableInterface;

/**
 * Holds nonce value and provide it when casted to string.
 *
 * @since [*next-version*]
 */
class WpNonce implements StringableInterface
{
    /**
     * Nonce value holder.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $nonce;

    /**
     * WpNonce constructor.
     *
     * @since [*next-version*]
     */
    public function __construct($id)
    {
        $this->nonce = \wp_create_nonce($id);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __toString()
    {
        return $this->nonce;
    }
}

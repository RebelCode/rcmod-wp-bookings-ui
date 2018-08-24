<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use WP_Screen;

/**
 * Trait RegisterApplicationPagesCapable for adding functionality to register the application page.
 *
 * Application page is any WP page where UI application should be launched. The page determined by
 * the WP Screen instance ID.
 *
 * @see WP_Screen
 * @since [*next-version*]
 */
trait RegisterApplicationPagesCapable
{
    /**
     * The map of registered pages to generated IDs.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $applicationPages = [];

    /**
     * Register some page as application page.
     *
     * @since [*next-version*]
     *
     * @param string $id          Human readable ID of the page.
     * @param string $generatedId Generated identifier of of string.
     */
    protected function _registerApplicationPage($id, $generatedId)
    {
        $this->applicationPages[$id] = $generatedId;
    }

    /**
     * Get all registered application pages.
     *
     * @since [*next-version*]
     *
     * @return array The map of registered pages to generated IDs.
     */
    protected function _getApplicationPages()
    {
        return $this->applicationPages;
    }
}

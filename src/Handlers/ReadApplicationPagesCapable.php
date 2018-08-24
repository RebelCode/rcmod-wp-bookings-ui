<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use WP_Screen;

/**
 * Trait ReadApplicationPagesCapable for adding functionality to get and check application page.
 *
 * Application page is any WP page where UI application should be launched. The page determined by
 * the WP Screen instance ID.
 *
 * @see WP_Screen
 * @since [*next-version*]
 */
trait ReadApplicationPagesCapable
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
     * Check that the user is on application page.
     *
     * @since [*next-version*]
     *
     * @return bool Whether the user is on application page.
     */
    protected function _isOnApplicationPage()
    {
        return in_array($this->_getCurrentScreenId(), $this->applicationPages);
    }

    /**
     * Check that the user is on given application page.
     *
     * @since [*next-version*]
     *
     * @param string $id Page id to check.
     *
     * @return bool Whether the user is on the given application page.
     */
    protected function _isOnPage($id)
    {
        return $this->_getCurrentScreenId() === $this->applicationPages[$id];
    }

    /**
     * Get current application page ID (not generated ID).
     *
     * @since [*next-version*]
     *
     * @return string ID of the application page.
     */
    protected function _getCurrentApplicationPage()
    {
        return array_flip($this->applicationPages)[$this->_getCurrentScreenId()];
    }

    /**
     * Get current screen identifier.
     *
     * @since [*next-version*]
     *
     * @return int|string Screen ID.
     */
    protected function _getCurrentScreenId()
    {
        return get_current_screen()->id;
    }

    /**
     * Set all registered application pages for current instance.
     *
     * @since [*next-version*]
     */
    protected function _setApplicationPages($pages)
    {
        $this->applicationPages = $pages;
    }
}

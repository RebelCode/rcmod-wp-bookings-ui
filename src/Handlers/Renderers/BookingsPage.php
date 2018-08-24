<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

use RebelCode\Bookings\WordPress\Module\TemplateManager;

/**
 * Class BookingsPage for rendering content of the bookings page.
 *
 * @since [*next-version*]
 */
class BookingsPage extends RenderHandler
{
    /**
     * The event based template manager instance.
     *
     * @since [*next-version*]
     *
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * BookingsPage constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateManager $templateManager The event based template manager instance.
     */
    public function __construct($templateManager)
    {
        $this->templateManager = $templateManager;
    }

    /**
     * Render bookings page template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        return $this->templateManager->render('booking/bookings-page');
    }
}

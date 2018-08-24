<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

use RebelCode\Bookings\WordPress\Module\TemplateManager;

/**
 * Class ScreenOptions for rendering content of the screen options.
 *
 * @since [*next-version*]
 */
class ScreenOptions extends RenderHandler
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
     * ScreenOptions constructor.
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
     * Render screen options template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        return $this->templateManager->render('booking/screen-options');
    }
}

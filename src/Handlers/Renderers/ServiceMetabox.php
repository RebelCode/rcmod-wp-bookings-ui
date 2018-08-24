<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

use RebelCode\Bookings\WordPress\Module\TemplateManager;

/**
 * Class ServiceMetabox for rendering content of the service's metabox.
 *
 * @since [*next-version*]
 */
class ServiceMetabox extends RenderHandler
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
     * ServiceMetabox constructor.
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
     * Render service metabox template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        return $this->templateManager->render('availability/metabox');
    }
}

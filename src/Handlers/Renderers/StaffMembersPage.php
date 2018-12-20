<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

/**
 * Class StaffMembersPage for rendering content of the staff members page.
 *
 * @since [*next-version*]
 */
class StaffMembersPage extends RenderHandler
{
    /**
     * Page template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $pageTemplate;

    /**
     * The event based template manager instance.
     *
     * @since [*next-version*]
     *
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * ServicesPage constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface $pageTemplate    Page template.
     * @param TemplateManager   $templateManager The event based template manager instance.
     */
    public function __construct($pageTemplate, $templateManager)
    {
        $this->pageTemplate    = $pageTemplate;
        $this->templateManager = $templateManager;
    }

    /**
     * Render page template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        /*
         * Enqueue WP media scripts for selecting photo using WP image selector.
         */
        wp_enqueue_media();

        return $this->pageTemplate->render([
            'components' => $this->templateManager->render('components'),
        ]);
    }
}

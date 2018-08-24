<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

/**
 * Class Settings for rendering content of the settings page.
 *
 * @since [*next-version*]
 */
class SettingsPage extends RenderHandler
{
    /**
     * Settings page template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $settingsTemplate;

    /**
     * General tab template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $generalTabTemplate;

    /**
     * The event based template manager instance.
     *
     * @since [*next-version*]
     *
     * @var TemplateManager
     */
    protected $templateManager;

    /**
     * SettingsPage constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface $settingsTemplate   Settings page template.
     * @param TemplateInterface $generalTabTemplate General tab template.
     * @param TemplateManager   $templateManager    The event based template manager instance.
     */
    public function __construct($settingsTemplate, $generalTabTemplate, $templateManager)
    {
        $this->settingsTemplate   = $settingsTemplate;
        $this->generalTabTemplate = $generalTabTemplate;
        $this->templateManager    = $templateManager;
    }

    /**
     * Render settings page template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        return $this->settingsTemplate->render([
            'generalSettingsTab' => $this->generalTabTemplate->render(),
            'components'         => $this->templateManager->render('components'),
        ]);
    }
}

<?php

namespace RebelCode\Bookings\WordPress\Module;

use Psr\EventManager\EventManagerInterface;

/**
 * Class TemplateManager
 *
 * Template manager is responsible for registering templates in given
 * event manager from container.
 *
 * It register all defined templates as actions in event manager. All templates
 * are prefixed with `eddbk` or any other given prefix. We need prefix when we
 * tweaking from outside.
 *
 * In this module we can work without referencing to the
 * prefix by using `render($templateActionName)` method.
 *
 * Inside render method we will apply prefix and trigger action in event manager after this.
 *
 * @package RebelCode\Bookings\Wordpress\Module
 */
class TemplateManager
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Template prefix
     *
     * @var
     */
    protected $prefix;

    /**
     * TemplateManager constructor.
     *
     * @param EventManagerInterface $eventManager
     * @param string $prefix
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(EventManagerInterface $eventManager, $prefix = 'eddbk')
    {
        $this->eventManager = $eventManager;
        $this->prefix = $prefix;
    }

    /**
     * Register all templates in event manager.
     *
     * @param $templates array Array of templates to be registered.
     */
    public function registerTemplates($templates)
    {
        $this->_register($templates);
    }

    /**
     * Render template by given template action.
     *
     * @param $template
     * @return mixed
     */
    public function render($template)
    {
        return $this->eventManager->trigger(
            $this->_makeTemplateActionName($template)
        );
    }

    /**
     * Register given templates in event manager.
     *
     * @param $templates
     */
    protected function _register($templates)
    {
        foreach ($templates as $template) {
            $templateFilePath = WP_BOOKINGS_UI_MODULE_DIR . '/templates/' . $template . '.phtml';
            $actionName = $this->_makeTemplateActionName($template);

            $this->eventManager->attach($actionName, function () use ($templateFilePath) {
                return $this->_renderFile($templateFilePath);
            });
        }
    }

    /**
     * Render file.
     *
     * @param $realFilePath
     * @return string
     */
    protected function _renderFile($realFilePath)
    {
        ob_start();
        include($realFilePath);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Create action in event manager according template name.
     *
     * @param $templatePath
     * @return string
     */
    protected function _makeTemplateActionName($templatePath)
    {
        return $this->prefix . '_' . $templatePath;
    }
}
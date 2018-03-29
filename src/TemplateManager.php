<?php

namespace RebelCode\Bookings\WordPress\Module;

use Psr\Container\ContainerInterface;
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
     * @param ContainerInterface $c
     * @param string $prefix
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $c, $prefix = 'eddbk')
    {
        $this->eventManager = $c->get('event-manager');
        $this->prefix = $prefix;
    }

    /**
     * Register all templates in event manager.
     */
    public function registerTemplates()
    {
        $this->_register(array_merge(
            $this->_getGeneralTemplates(),
            $this->_getServiceMetaboxTemplates(),
            $this->_getBookingsTemplates()
        ));
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
            $templateFilePath = __DIR__ . '/../templates/' . $template . '.phtml';
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

    /**
     * General templates.
     *
     * @return array
     */
    protected function _getGeneralTemplates()
    {
        return [
            'components',
            'main',
        ];
    }

    /**
     * Service metabox templates.
     *
     * @return array
     */
    protected function _getServiceMetaboxTemplates()
    {
        return [
            'availability/metabox',
            'availability/service-availability-editor',
            'availability/tab-availability',
            'availability/tab-display-options',
            'availability/tab-session-length',
        ];
    }

    /**
     * Bookings templates.
     *
     * @return array
     */
    protected function _getBookingsTemplates()
    {
        return [
            'booking/booking-editor',
            'booking/bookings-calendar-view',
            'booking/bookings-list-view',
            'booking/bookings-page',
            'booking/general',
            'booking/screen-options',
        ];
    }
}
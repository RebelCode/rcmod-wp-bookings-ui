<?php

namespace RebelCode\Bookings\Wordpress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

class UIModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerFactoryInterface $containerFactory The factory for creating container instances.
     * @throws \Dhii\Exception\InternalException
     */
    public function __construct(ContainerFactoryInterface $containerFactory)
    {
        $this->_initModule(
            $containerFactory,
            'bookings-ui-module',
            ['wp-events'],
            $this->_loadPhpConfigFile(__DIR__ . '/../config.php')
        );
    }

    /**
     * @inheritdoc
     */
    public function setup()
    {
        return $this->_createContainer([
            'template-manager' => function (ContainerInterface $c) {
                $templateManager = new TemplateManager($c);
                $templateManager->registerTemplates();
                return $templateManager;
            }
        ]);
    }

    /**
     * @inheritdoc
     */
    public function run(ContainerInterface $c = null)
    {
        /* @var $eventManager EventManagerInterface */
        $eventManager = $c->get('event-manager');

        $eventManager->attach('admin_init', function () use ($c) {
            $this->_adminInit($c);
        });
    }

    /**
     * Register hook on admin init which will register everything else.
     *
     * @param ContainerInterface $c
     */
    protected function _adminInit(ContainerInterface $c)
    {
        add_meta_box(
            $this->_getConfig()['metabox']['id'],
            $this->_getConfig()['metabox']['title'],
            function () use ($c) {
                return $this->_renderMetabox($c);
            },
            $this->_getConfig()['metabox']['post_type']
        );
    }

    /**
     * Register metabox for service's bookings settings.
     *
     * @param ContainerInterface $c
     * @return string
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function _renderMetabox($c)
    {
        return $c->get('template-manager')->render('availability/metabox');
    }
}
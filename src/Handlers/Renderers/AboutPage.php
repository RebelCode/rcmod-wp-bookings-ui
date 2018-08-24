<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers\Renderers;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use stdClass;

/**
 * Class AboutPage for rendering content of the about page.
 *
 * @since [*next-version*]
 */
class AboutPage extends RenderHandler
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /**
     * About page template.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $aboutTemplate;

    /**
     * Map of urls identifiers to real urls.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $urls;

    /**
     * AboutPage constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface           $aboutTemplate About page template.
     * @param array|stdClass|MapInterface $urls          Map of urls identifiers to real urls.
     */
    public function __construct(TemplateInterface $aboutTemplate, $urls)
    {
        $this->aboutTemplate = $aboutTemplate;
        $this->urls          = $urls;
    }

    /**
     * Render about page template.
     *
     * @since [*next-version*]
     *
     * @return string
     */
    protected function _render()
    {
        return $this->aboutTemplate->render([
            'edd_ref_url'         => $this->_containerGet($this->urls, 'edd_ref'),
            'rebelcode_url'       => $this->_containerGet($this->urls, 'rebelcode'),
            'how_to_wizard_url'   => $this->_containerGet($this->urls, 'how_to_wizard'),
            'get_started_url'     => $this->_containerGet($this->urls, 'get_started'),
            'feature_request_url' => $this->_containerGet($this->urls, 'feature_request'),
            'contact_us_url'      => $this->_containerGet($this->urls, 'contact_us'),
            'enter_license_url'   => admin_url($this->_containerGet($this->urls, 'license')),
        ]);
    }
}

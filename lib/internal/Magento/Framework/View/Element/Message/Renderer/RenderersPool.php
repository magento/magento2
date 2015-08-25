<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

class RenderersPool implements PoolInterface
{
    /**
     * @var RendererInterface[]
     */
    private $renderers;

    /**
     * @var array
     */
    private $renderersConfiguration;

    /**
     * @param array $renderers
     * @param array $renderersConfiguration
     */
    public function __construct(
        array $renderers = [],
        array $renderersConfiguration = []
    ) {
        array_walk(
            $renderers,
            function (RendererInterface $renderer) {
                return $renderer;
            }
        );

        $this->renderers = $renderers;
        $this->renderersConfiguration = $renderersConfiguration;
    }

    /**
     * Returns Renderer for specified identifier
     *
     * @param string $identifier
     * @return RendererInterface | null
     */
    public function get($identifier)
    {
        if (!isset($this->renderers[$identifier])) {
            return null;
        }

        $renderer = $this->renderers[$identifier];
        $renderer->initialize(
            !isset($this->renderersConfiguration[$identifier])
            ? null
            : $this->renderersConfiguration[$identifier]
        );

        return $renderer;
    }
}

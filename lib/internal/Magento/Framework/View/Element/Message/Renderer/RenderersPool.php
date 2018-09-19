<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param array $renderers
     */
    public function __construct(
        array $renderers = []
    ) {
        array_walk(
            $renderers,
            function (RendererInterface $renderer) {
                return $renderer;
            }
        );

        $this->renderers = $renderers;
    }

    /**
     * Returns Renderer for specified identifier
     *
     * @param string $rendererCode
     * @return RendererInterface | null
     */
    public function get($rendererCode)
    {
        return !isset($this->renderers[$rendererCode])
                ? null
                :$this->renderers[$rendererCode];
    }
}

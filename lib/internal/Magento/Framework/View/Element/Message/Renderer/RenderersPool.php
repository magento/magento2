<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

/**
 * Class \Magento\Framework\View\Element\Message\Renderer\RenderersPool
 *
 * @since 2.0.0
 */
class RenderersPool implements PoolInterface
{
    /**
     * @var RendererInterface[]
     * @since 2.0.0
     */
    private $renderers;

    /**
     * @param array $renderers
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get($rendererCode)
    {
        return !isset($this->renderers[$rendererCode])
                ? null
                :$this->renderers[$rendererCode];
    }
}

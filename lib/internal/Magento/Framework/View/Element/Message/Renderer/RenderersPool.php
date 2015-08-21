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
    private $renders;

    /**
     * @param array $renders
     */
    public function __construct(
        array $renders = []
    ) {
        array_walk(
            $renders,
            function (RendererInterface $renderer) {
                return $renderer;
            }
        );

        $this->renders = $renders;
    }

    /**
     * Returns Renderer for specified identifier
     *
     * @param string $identifier
     * @return RendererInterface | null
     */
    public function get($identifier)
    {
        return !isset($this->renders[$identifier]) ? null : $this->renders[$identifier];
    }
}

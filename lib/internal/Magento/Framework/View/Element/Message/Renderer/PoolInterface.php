<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

interface PoolInterface
{
    /**
     * Returns Renderer for specified identifier
     *
     * @param string $identifier
     * @return RendererInterface | null
     */
    public function get($identifier);
}

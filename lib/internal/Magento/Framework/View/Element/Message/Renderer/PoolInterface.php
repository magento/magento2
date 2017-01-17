<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

interface PoolInterface
{
    /**
     * Returns Renderer for specified identifier
     *
     * @param string $rendererCode
     * @return RendererInterface | null
     */
    public function get($rendererCode);
}

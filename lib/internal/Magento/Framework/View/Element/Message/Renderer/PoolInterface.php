<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

/**
 * Interface \Magento\Framework\View\Element\Message\Renderer\PoolInterface
 *
 * @since 2.0.0
 */
interface PoolInterface
{
    /**
     * Returns Renderer for specified identifier
     *
     * @param string $rendererCode
     * @return RendererInterface | null
     * @since 2.0.0
     */
    public function get($rendererCode);
}

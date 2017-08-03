<?php
/**
 * No route handler interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

/**
 * Interface \Magento\Framework\App\Router\NoRouteHandlerInterface
 *
 * @since 2.0.0
 */
interface NoRouteHandlerInterface
{
    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     * @since 2.0.0
     */
    public function process(\Magento\Framework\App\RequestInterface $request);
}

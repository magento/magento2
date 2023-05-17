<?php
/**
 * No route handler interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Router;

/**
 * Interface \Magento\Framework\App\Router\NoRouteHandlerInterface
 *
 * @api
 */
interface NoRouteHandlerInterface
{
    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function process(\Magento\Framework\App\RequestInterface $request);
}

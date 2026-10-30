<?php
/**
 * No route handler interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
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

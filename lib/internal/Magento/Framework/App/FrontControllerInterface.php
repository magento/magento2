<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Application front controller responsible for dispatching application requests.
 * Front controller contains logic common for all actions.
 * Evary application area has own front controller
 *
 * @api
 * @since 100.0.2
 */
interface FrontControllerInterface
{
    /**
     * Dispatch application action
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request);
}

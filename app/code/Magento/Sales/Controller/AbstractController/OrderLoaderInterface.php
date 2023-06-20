<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;

/**
 * Interface \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
 * @api
 *
 */
interface OrderLoaderInterface
{
    /**
     * Load order
     *
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\Controller\ResultInterface
     */
    public function load(RequestInterface $request);
}

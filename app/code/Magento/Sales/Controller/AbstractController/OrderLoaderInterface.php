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
 *
 * @since 2.0.0
 */
interface OrderLoaderInterface
{
    /**
     * Load order
     *
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function load(RequestInterface $request);
}

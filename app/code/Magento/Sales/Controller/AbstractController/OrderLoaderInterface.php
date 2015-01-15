<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;

interface OrderLoaderInterface
{
    /**
     * Load order
     *
     * @param RequestInterface $request
     * @return \Magento\Sales\Model\Order
     */
    public function load(RequestInterface $request);
}

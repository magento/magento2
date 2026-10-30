<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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

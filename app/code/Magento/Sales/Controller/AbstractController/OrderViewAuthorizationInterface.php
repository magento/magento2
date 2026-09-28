<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\AbstractController;

/**
 * Interface \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
 * @api
 *
 */
interface OrderViewAuthorizationInterface
{
    /**
     * Check if order can be viewed by user
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canView(\Magento\Sales\Model\Order $order);
}

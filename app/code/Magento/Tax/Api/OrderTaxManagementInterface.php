<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api;

interface OrderTaxManagementInterface
{
    /**
     * Get taxes applied to an order
     *
     * @param int $orderId
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderTaxDetails($orderId);
}

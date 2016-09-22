<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api;

/**
 * Interface OrderTaxManagementInterface
 * @api
 */
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

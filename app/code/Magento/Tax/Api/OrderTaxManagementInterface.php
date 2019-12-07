<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api;

/**
 * Interface OrderTaxManagementInterface
 * @api
 * @since 100.0.2
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

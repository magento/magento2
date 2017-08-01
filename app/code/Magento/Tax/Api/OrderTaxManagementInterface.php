<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api;

/**
 * Interface OrderTaxManagementInterface
 * @api
 * @since 2.0.0
 */
interface OrderTaxManagementInterface
{
    /**
     * Get taxes applied to an order
     *
     * @param int $orderId
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function getOrderTaxDetails($orderId);
}

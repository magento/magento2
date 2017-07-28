<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * @api
 *
 * @since 2.0.0
 */
interface OrderCustomerManagementInterface
{
    /**
     * Create customer account for order
     *
     * @param int $orderId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @since 2.0.0
     */
    public function create($orderId);
}

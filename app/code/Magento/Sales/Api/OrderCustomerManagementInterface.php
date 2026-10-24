<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Api;

/**
 * @api
 *
 * @since 100.0.2
 */
interface OrderCustomerManagementInterface
{
    /**
     * Create customer account for order
     *
     * @param int $orderId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function create($orderId);
}

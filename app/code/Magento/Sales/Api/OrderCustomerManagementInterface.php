<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/** @api */
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

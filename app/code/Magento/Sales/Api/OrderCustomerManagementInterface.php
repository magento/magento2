<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

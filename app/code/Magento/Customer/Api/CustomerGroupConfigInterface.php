<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

/**
 * Interface for system configuration operations for customer groups.
 *
 * @api
 * @since 100.2.0
 */
interface CustomerGroupConfigInterface
{
    /**
     * Set system default customer group.
     *
     * @param int $id
     * @return int
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    public function setDefaultCustomerGroup($id);
}

<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface CustomerNameGenerationInterface
 *
 * @api
 * @since 100.1.0
 */
interface CustomerNameGenerationInterface
{
    /**
     * Concatenate all customer name parts into full customer name.
     *
     * @param CustomerInterface $customerData
     * @return string
     * @since 100.1.0
     */
    public function getCustomerName(CustomerInterface $customerData);
}

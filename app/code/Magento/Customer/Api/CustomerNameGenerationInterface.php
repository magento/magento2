<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface CustomerNameGenerationInterface
 *
 * @api
 * @since 2.1.0
 */
interface CustomerNameGenerationInterface
{
    /**
     * Concatenate all customer name parts into full customer name.
     *
     * @param CustomerInterface $customerData
     * @return string
     * @since 2.1.0
     */
    public function getCustomerName(CustomerInterface $customerData);
}

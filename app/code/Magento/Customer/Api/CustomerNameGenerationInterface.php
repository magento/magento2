<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface CustomerNameGenerationInterface
 *
 * @api
 */
interface CustomerNameGenerationInterface
{
    /**
     * Concatenate all customer name parts into full customer name.
     *
     * @param CustomerInterface $customerData
     * @return string
     */
    public function getCustomerName(CustomerInterface $customerData);
}

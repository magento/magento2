<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

use Magento\Tax\Api\Data\TaxRateInterface;

interface TaxRateManagementInterface
{
    /**
     * Get rates by customerTaxClassId and productTaxClassId
     *
     * @param int $customerTaxClassId
     * @param int $productTaxClassId
     * @return TaxRateInterface[]
     */
    public function getRatesByCustomerAndProductTaxClassId($customerTaxClassId, $productTaxClassId);
}

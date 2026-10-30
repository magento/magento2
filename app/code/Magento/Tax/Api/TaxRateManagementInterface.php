<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Api;

use Magento\Tax\Api\Data\TaxRateInterface;

/**
 * Interface for managing tax rates.
 * @api
 * @since 100.0.2
 */
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

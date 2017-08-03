<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

use Magento\Tax\Api\Data\TaxRateInterface;

/**
 * Interface for managing tax rates.
 * @api
 * @since 2.0.0
 */
interface TaxRateManagementInterface
{
    /**
     * Get rates by customerTaxClassId and productTaxClassId
     *
     * @param int $customerTaxClassId
     * @param int $productTaxClassId
     * @return TaxRateInterface[]
     * @since 2.0.0
     */
    public function getRatesByCustomerAndProductTaxClassId($customerTaxClassId, $productTaxClassId);
}

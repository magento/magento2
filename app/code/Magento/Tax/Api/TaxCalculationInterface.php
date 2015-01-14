<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

interface TaxCalculationInterface
{
    /**#@+
     * Type of calculation used
     */
    const CALC_UNIT_BASE = 'UNIT_BASE_CALCULATION';
    const CALC_ROW_BASE = 'ROW_BASE_CALCULATION';
    const CALC_TOTAL_BASE = 'TOTAL_BASE_CALCULATION';
    /**#@-*/

    /**
     * Calculate Tax
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails
     * @param null|int $storeId
     * @param bool $round
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    public function calculateTax(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails,
        $storeId = null,
        $round = true
    );

    /**
     * Get default rate request
     *
     * @param int $productTaxClassID
     * @param int $customerId
     * @param string $storeId
     * @return float
     */
    public function getDefaultCalculatedRate($productTaxClassID, $customerId = null, $storeId = null);

    /**
     * Get rate request
     *
     * @param int $productTaxClassID
     * @param int $customerId
     * @param string $storeId
     * @return float
     */
    public function getCalculatedRate($productTaxClassID, $customerId = null, $storeId = null);
}

<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1;

interface TaxCalculationServiceInterface
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
     * @param \Magento\Tax\Service\V1\Data\QuoteDetails $quoteDetails
     * @param null|int $storeId
     * @return \Magento\Tax\Service\V1\Data\TaxDetails
     */
    public function calculateTax(\Magento\Tax\Service\V1\Data\QuoteDetails $quoteDetails, $storeId = null);

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

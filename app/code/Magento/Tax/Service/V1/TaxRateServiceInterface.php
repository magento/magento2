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

interface TaxRateServiceInterface
{
    /**
     * Create tax rate
     *
     * @param \Magento\Tax\Service\V1\Data\TaxRate $taxRate
     * @return \Magento\Tax\Service\V1\Data\TaxRate
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Exception If something went wrong while creating the TaxRate.
     */
    public function createTaxRate(\Magento\Tax\Service\V1\Data\TaxRate $taxRate);

    /**
     * Get tax rate
     *
     * @param int $rateId
     * @return \Magento\Tax\Service\V1\Data\TaxRate
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTaxRate($rateId);

    /**
     * Update given tax rate
     *
     * @param \Magento\Tax\Service\V1\Data\TaxRate $taxRate
     * @return bool
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the TaxRate to update can't be found in the system.
     * @throws \Exception If something went wrong while performing the update.
     */
    public function updateTaxRate(\Magento\Tax\Service\V1\Data\TaxRate $taxRate);

    /**
     * Delete tax rate
     *
     * @param int $rateId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     */
    public function deleteTaxRate($rateId);

    /**
     * Search TaxRates
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Tax\Service\V1\Data\TaxRateSearchResults containing Data\TaxRate objects
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     */
    public function searchTaxRates(\Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria);
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

/**
 * Tax rate CRUD interface.
 * @api
 * @since 2.0.0
 */
interface TaxRateRepositoryInterface
{
    /**
     * Create or update tax rate
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return \Magento\Tax\Api\Data\TaxRateInterface
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Exception If something went wrong while creating the TaxRate.
     * @since 2.0.0
     */
    public function save(\Magento\Tax\Api\Data\TaxRateInterface $taxRate);

    /**
     * Get tax rate
     *
     * @param int $rateId
     * @return \Magento\Tax\Api\Data\TaxRateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function get($rateId);

    /**
     * Delete tax rate
     *
     * @param int $rateId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     * @since 2.0.0
     */
    public function deleteById($rateId);

    /**
     * Search TaxRates
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#TaxRateRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Tax\Api\Data\TaxRateSearchResultsInterface containing Data\TaxRateInterface objects
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete tax rate
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     * @since 2.0.0
     */
    public function delete(\Magento\Tax\Api\Data\TaxRateInterface $taxRate);
}

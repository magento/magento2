<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

/**
 * Tax class CRUD interface.
 * @api
 * @since 100.0.2
 */
interface TaxClassRepositoryInterface
{
    /**
     * Get a tax class with the given tax class id.
     *
     * @param int $taxClassId
     * @return \Magento\Tax\Api\Data\TaxClassInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If tax class with $taxClassId does not exist
     */
    public function get($taxClassId);

    /**
     * Retrieve tax classes which match a specific criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://developer.adobe.com/commerce/webapi/rest/attributes#TaxClassRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Tax\Api\Data\TaxClassSearchResultsInterface containing Data\TaxClassInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Create a Tax Class
     *
     * @param \Magento\Tax\Api\Data\TaxClassInterface $taxClass
     * @return string id for the newly created Tax class
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Tax\Api\Data\TaxClassInterface $taxClass);

    /**
     * Delete a tax class
     *
     * @param \Magento\Tax\Api\Data\TaxClassInterface $taxClass
     * @return bool True if the tax class was deleted, false otherwise
     * @throws \Magento\Framework\Exception\NoSuchEntityException If tax class with $taxClassId does not exist
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\Tax\Api\Data\TaxClassInterface $taxClass);

    /**
     * Delete a tax class with the given tax class id.
     *
     * @param int $taxClassId
     * @return bool True if the tax class was deleted, false otherwise
     * @throws \Magento\Framework\Exception\NoSuchEntityException If tax class with $taxClassId does not exist
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($taxClassId);
}

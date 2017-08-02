<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

/**
 * Tax rule CRUD interface.
 * @api
 * @since 2.0.0
 */
interface TaxRuleRepositoryInterface
{
    /**
     * Get TaxRule
     *
     * @param int $ruleId
     * @return \Magento\Tax\Api\Data\TaxRuleInterface
     * @since 2.0.0
     */
    public function get($ruleId);

    /**
     * Save TaxRule
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @return \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Exception If something went wrong while performing the update.
     * @since 2.0.0
     */
    public function save(\Magento\Tax\Api\Data\TaxRuleInterface $rule);

    /**
     * Delete TaxRule
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     * @since 2.0.0
     */
    public function delete(\Magento\Tax\Api\Data\TaxRuleInterface $rule);

    /**
     * Delete TaxRule
     *
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     * @since 2.0.0
     */
    public function deleteById($ruleId);

    /**
     * Search TaxRules
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#TaxRuleRepositoryInterface to
     * determine which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Tax\Api\Data\TaxRuleSearchResultsInterface containing TaxRuleInterface objects
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api;

interface TaxRuleRepositoryInterface
{
    /**
     * Get TaxRule
     *
     * @param int $ruleId
     * @return \Magento\Tax\Api\Data\TaxRuleInterface
     */
    public function get($ruleId);

    /**
     * Save TaxRule
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @return \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @throws \Magento\Framework\Exception\InputException If input is invalid or required input is missing.
     * @throws \Exception If something went wrong while performing the update.
     */
    public function save(\Magento\Tax\Api\Data\TaxRuleInterface $rule);

    /**
     * Delete TaxRule
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     */
    public function delete(\Magento\Tax\Api\Data\TaxRuleInterface $rule);

    /**
     * Delete TaxRule
     *
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If no TaxRate with the given ID can be found.
     * @throws \Exception If something went wrong while performing the delete.
     */
    public function deleteById($ruleId);

    /**
     * Search TaxRules
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magento\Tax\Api\Data\TaxRuleSearchResultsInterface containing TaxRuleInterface objects
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);
}

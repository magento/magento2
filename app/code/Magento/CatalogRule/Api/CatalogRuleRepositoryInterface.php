<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Api;

/**
 * Interface CatalogRuleRepositoryInterface
 * @api
 */
interface CatalogRuleRepositoryInterface
{
    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($ruleId);

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($ruleId);
}

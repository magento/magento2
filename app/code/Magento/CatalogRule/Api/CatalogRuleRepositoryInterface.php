<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Api;

interface CatalogRuleRepositoryInterface
{
    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     */
    public function save(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     */
    public function get($ruleId);

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return bool
     */
    public function delete(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     */
    public function deleteById($ruleId);
}

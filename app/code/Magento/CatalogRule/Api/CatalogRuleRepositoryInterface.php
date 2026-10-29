<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogRule\Api;

/**
 * Interface CatalogRuleRepositoryInterface
 * @api
 * @since 100.1.0
 */
interface CatalogRuleRepositoryInterface
{
    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 100.1.0
     */
    public function save(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return \Magento\CatalogRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 100.1.0
     */
    public function get($ruleId);

    /**
     * @param \Magento\CatalogRule\Api\Data\RuleInterface $rule
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @since 100.1.0
     */
    public function delete(\Magento\CatalogRule\Api\Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @since 100.1.0
     */
    public function deleteById($ruleId);
}

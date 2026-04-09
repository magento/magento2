<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\Rule;

class GetAffectedProductIds
{
    /**
     * @param CollectionFactory $ruleCollectionFactory
     * @param RuleResourceModel $ruleResourceModel
     */
    public function __construct(
        private readonly CollectionFactory $ruleCollectionFactory,
        private readonly RuleResourceModel $ruleResourceModel
    ) {
    }

    /**
     * Get affected product ids by rule ids
     *
     * @param array $ids
     * @return array
     */
    public function execute(array $ids): array
    {
        $productIds = $this->ruleResourceModel->getProductIdsByRuleIds($ids);
        $rules = $this->ruleCollectionFactory->create()
            ->addFieldToFilter('rule_id', ['in' => array_map('intval', $ids)]);
        foreach ($rules as $rule) {
            /** @var Rule $rule */
            array_push($productIds, ...array_keys($rule->getMatchingProductIds()));
        }
        return array_values(array_unique($productIds));
    }
}

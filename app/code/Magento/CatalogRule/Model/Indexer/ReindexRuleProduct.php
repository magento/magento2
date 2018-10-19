<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ObjectManager;

/**
 * Reindex rule relations with products.
 */
class ReindexRuleProduct
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var ActiveTableSwitcher
     */
    private $activeTableSwitcher;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper|null $tableSwapper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        TableSwapper $tableSwapper = null
    ) {
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Reindex information about rule relations with products.
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param int $batchCount
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(
        \Magento\CatalogRule\Model\Rule $rule,
        $batchCount,
        $useAdditionalTable = false
    ) {
        if (!$rule->getIsActive() || empty($rule->getWebsiteIds())) {
            return false;
        }

        $connection = $this->resource->getConnection();
        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }

        \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');

        $indexTable = $this->resource->getTableName('catalogrule_product');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product')
            );
        }

        $ruleId = $rule->getId();
        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + \Magento\CatalogRule\Model\Indexer\IndexBuilder::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = [];

        foreach ($productIds as $productId => $validationByWebsite) {
            foreach ($websiteIds as $websiteId) {
                if (empty($validationByWebsite[$websiteId])) {
                    continue;
                }
                foreach ($customerGroupIds as $customerGroupId) {
                    $rows[] = [
                        'rule_id' => $ruleId,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'website_id' => $websiteId,
                        'customer_group_id' => $customerGroupId,
                        'product_id' => $productId,
                        'action_operator' => $actionOperator,
                        'action_amount' => $actionAmount,
                        'action_stop' => $actionStop,
                        'sort_order' => $sortOrder,
                    ];

                    if (count($rows) == $batchCount) {
                        $connection->insertMultiple($indexTable, $rows);
                        $rows = [];
                    }
                }
            }
        }
        if (!empty($rows)) {
            $connection->insertMultiple($indexTable, $rows);
        }
        return true;
    }
}

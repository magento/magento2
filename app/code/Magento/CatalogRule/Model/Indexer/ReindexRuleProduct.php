<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reindex rule relations with products.
 */
class ReindexRuleProduct
{
    private const ADMIN_WEBSITE_ID = 0;

    /**
     * @var ResourceConnection
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
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var bool
     */
    private $useWebsiteTimezone;

    /**
     * @param ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper $tableSwapper
     * @param TimezoneInterface $localeDate
     * @param bool $useWebsiteTimezone
     */
    public function __construct(
        ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        TableSwapper $tableSwapper,
        TimezoneInterface $localeDate,
        bool $useWebsiteTimezone = true
    ) {
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->tableSwapper = $tableSwapper;
        $this->localeDate = $localeDate;
        $this->useWebsiteTimezone = $useWebsiteTimezone;
    }

    /**
     * Reindex information about rule relations with products.
     *
     * @param Rule $rule
     * @param int $batchCount
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Rule $rule, $batchCount, $useAdditionalTable = false)
    {
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
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();
        $fromTimeInAdminTz = $this->parseDateByWebsiteTz((string)$rule->getFromDate(), self::ADMIN_WEBSITE_ID);
        $toTimeInAdminTz = $this->parseDateByWebsiteTz((string)$rule->getToDate(), self::ADMIN_WEBSITE_ID);
        $excludedWebsites = [];
        $ruleExtensionAttributes = $rule->getExtensionAttributes();
        if ($ruleExtensionAttributes && $ruleExtensionAttributes->getExcludeWebsiteIds()) {
            $excludedWebsites = $ruleExtensionAttributes->getExcludeWebsiteIds();
        }

        $rows = [];
        foreach ($websiteIds as $websiteId) {
            $fromTime = $this->useWebsiteTimezone
                ? $this->parseDateByWebsiteTz((string)$rule->getFromDate(), (int)$websiteId)
                : $fromTimeInAdminTz;
            $toTime = $this->useWebsiteTimezone
                ? $this->parseDateByWebsiteTz((string)$rule->getToDate(), (int)$websiteId)
                    + ($rule->getToDate() ? IndexBuilder::SECONDS_IN_DAY - 1 : 0)
                : $toTimeInAdminTz;

            foreach ($productIds as $productId => $validationByWebsite) {
                if (empty($validationByWebsite[$websiteId])) {
                    continue;
                }

                foreach ($customerGroupIds as $customerGroupId) {
                    if (!array_key_exists($customerGroupId, $excludedWebsites)
                        || !in_array((int)$websiteId, array_values($excludedWebsites[$customerGroupId]), true)
                    ) {
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

                        if (count($rows) === (int) $batchCount) {
                            $connection->insertMultiple($indexTable, $rows);
                            $rows = [];
                        }
                    }
                }
            }
        }
        if (!empty($rows)) {
            $connection->insertMultiple($indexTable, $rows);
        }

        return true;
    }

    /**
     * Parse date value by the timezone of the website
     *
     * @param string $date
     * @param int $websiteId
     * @return int
     */
    private function parseDateByWebsiteTz(string $date, int $websiteId): int
    {
        if (empty($date)) {
            return 0;
        }

        $websiteTz = $this->localeDate->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $websiteId);
        $dateTime = new \DateTime($date, new \DateTimeZone($websiteTz));

        return $dateTime->getTimestamp();
    }
}

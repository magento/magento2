<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Reindex rule relations with products.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var DynamicBatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @param ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param TableSwapper $tableSwapper
     * @param TimezoneInterface $localeDate
     * @param bool $useWebsiteTimezone
     * @param DynamicBatchSizeCalculator|null $batchSizeCalculator
     */
    public function __construct(
        ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        TableSwapper $tableSwapper,
        TimezoneInterface $localeDate,
        bool $useWebsiteTimezone = true,
        ?DynamicBatchSizeCalculator $batchSizeCalculator = null
    ) {
        $this->resource = $resource;
        $this->activeTableSwitcher = $activeTableSwitcher;
        $this->tableSwapper = $tableSwapper;
        $this->localeDate = $localeDate;
        $this->useWebsiteTimezone = $useWebsiteTimezone;
        $this->batchSizeCalculator = $batchSizeCalculator ??
            ObjectManager::getInstance()->get(DynamicBatchSizeCalculator::class);
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
        $websiteIds = $this->getWebsiteIdsAsArray($rule->getWebsiteIds());

        \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');

        $indexTable = $this->getIndexTableName($useAdditionalTable);
        $ruleData = $this->prepareRuleData($rule);

        $productBatchSize = $this->batchSizeCalculator->getAttributeBatchSize();
        $totalBatches = $this->calculateTotalBatches(count($productIds), $productBatchSize);

        $rows = [];
        for ($batchIndex = 0; $batchIndex < $totalBatches; $batchIndex++) {
            $productBatch = array_slice($productIds, $batchIndex * $productBatchSize, $productBatchSize, true);
            $rows = $this->processBatch(
                $productBatch,
                $websiteIds,
                $rule,
                $ruleData,
                $indexTable,
                $connection,
                (int)$batchCount,
                $rows
            );
            unset($productBatch);
        }

        unset($productIds);

        if (!empty($rows)) {
            $connection->insertMultiple($indexTable, $rows);
        }

        $rule->_resetState();
        return true;
    }

    /**
     * Get website IDs as array
     *
     * @param string|array $websiteIds
     * @return array
     */
    private function getWebsiteIdsAsArray($websiteIds): array
    {
        return is_array($websiteIds) ? $websiteIds : explode(',', $websiteIds);
    }

    /**
     * Get index table name
     *
     * @param bool $useAdditionalTable
     * @return string
     */
    private function getIndexTableName(bool $useAdditionalTable): string
    {
        if ($useAdditionalTable) {
            return $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product')
            );
        }
        return $this->resource->getTableName('catalogrule_product');
    }

    /**
     * Prepare rule data for indexing
     *
     * @param Rule $rule
     * @return array
     */
    private function prepareRuleData(Rule $rule): array
    {
        $fromTimeInAdminTz = $this->parseDateByWebsiteTz((string)$rule->getFromDate(), self::ADMIN_WEBSITE_ID);
        $toTimeInAdminTz = $this->parseDateByWebsiteTz((string)$rule->getToDate(), self::ADMIN_WEBSITE_ID);

        $excludedWebsites = [];

        $ruleExtensionAttributes = $rule->getExtensionAttributes();
        if ($ruleExtensionAttributes && $ruleExtensionAttributes->getExcludeWebsiteIds()) {
            $excludedWebsites = $ruleExtensionAttributes->getExcludeWebsiteIds();
        } elseif ($rule->hasData('excluded_website_ids')) {
            $excludedWebsites = $rule->getData('excluded_website_ids') ?: [];
        }

        return [
            'rule_id' => (int)$rule->getId(),
            'customer_group_ids' => $rule->getCustomerGroupIds(),
            'sort_order' => (int)$rule->getSortOrder(),
            'action_operator' => $rule->getSimpleAction(),
            'action_amount' => $rule->getDiscountAmount(),
            'action_stop' => $rule->getStopRulesProcessing(),
            'from_time_admin_tz' => $fromTimeInAdminTz,
            'to_time_admin_tz' => $toTimeInAdminTz,
            'excluded_websites' => $excludedWebsites,
        ];
    }

    /**
     * Calculate total batches
     *
     * @param int $productCount
     * @param int $productBatchSize
     * @return int
     */
    private function calculateTotalBatches(int $productCount, int $productBatchSize): int
    {
        return $productCount > $productBatchSize ? (int)ceil($productCount / $productBatchSize) : 1;
    }

    /**
     * Process product batch
     *
     * @param array $productBatch
     * @param array $websiteIds
     * @param Rule $rule
     * @param array $ruleData
     * @param string $indexTable
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param int $batchCount
     * @param array $rows
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processBatch(
        array $productBatch,
        array $websiteIds,
        Rule $rule,
        array $ruleData,
        string $indexTable,
        $connection,
        int $batchCount,
        array $rows
    ): array {
        foreach ($websiteIds as $websiteId) {
            $websiteTimeData = $this->getWebsiteTimeData($rule, (int)$websiteId, $ruleData);

            foreach ($productBatch as $productId => $validationByWebsite) {
                if (empty($validationByWebsite[$websiteId])) {
                    continue;
                }

                $this->handleAntecedentRules(
                    $validationByWebsite,
                    (int)$productId,
                    $indexTable,
                    $connection,
                    (int)$ruleData['rule_id'],
                    $ruleData['sort_order']
                );

                $rows = $this->addCustomerGroupRows(
                    $rows,
                    $ruleData,
                    $websiteTimeData,
                    (int)$websiteId,
                    (int)$productId,
                    $indexTable,
                    $connection,
                    $batchCount
                );
            }
        }
        return $rows;
    }

    /**
     * Get website time data
     *
     * @param Rule $rule
     * @param int $websiteId
     * @param array $ruleData
     * @return array
     */
    private function getWebsiteTimeData(Rule $rule, int $websiteId, array $ruleData): array
    {
        if ($this->useWebsiteTimezone) {
            $fromTime = $this->parseDateByWebsiteTz((string)$rule->getFromDate(), $websiteId);
            $toTime = $this->parseDateByWebsiteTz((string)$rule->getToDate(), $websiteId)
                + ($rule->getToDate() ? IndexBuilder::SECONDS_IN_DAY - 1 : 0);
        } else {
            $fromTime = $ruleData['from_time_admin_tz'];
            $toTime = $ruleData['to_time_admin_tz'];
        }

        return ['from_time' => $fromTime, 'to_time' => $toTime];
    }

    /**
     * Handle antecedent rules
     *
     * @param array $validationByWebsite
     * @param int $productId
     * @param string $indexTable
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param int $ruleId
     * @param int $sortOrder
     * @return void
     */
    private function handleAntecedentRules(
        array $validationByWebsite,
        int $productId,
        string $indexTable,
        $connection,
        int $ruleId,
        int $sortOrder
    ): void {
        if (!isset($validationByWebsite['has_antecedent_rule'])) {
            return;
        }

        $antecedentRuleProductList = array_keys(
            $connection->fetchAssoc(
                $connection->select()->from($indexTable)
                    ->where('product_id = ?', $productId)
                    ->where('rule_id NOT IN (?)', $ruleId)
                    ->where('sort_order = ?', $sortOrder)
            )
        );
        $connection->delete($indexTable, ['rule_product_id IN (?)' => $antecedentRuleProductList]);
    }

    /**
     * Add customer group rows
     *
     * @param array $rows
     * @param array $ruleData
     * @param array $websiteTimeData
     * @param int $websiteId
     * @param int $productId
     * @param string $indexTable
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param int $batchCount
     * @return array
     */
    private function addCustomerGroupRows(
        array $rows,
        array $ruleData,
        array $websiteTimeData,
        int $websiteId,
        int $productId,
        string $indexTable,
        $connection,
        int $batchCount
    ): array {
        foreach ($ruleData['customer_group_ids'] as $customerGroupId) {
            $customerGroupId = (int)$customerGroupId;
            if ($this->isWebsiteExcluded($customerGroupId, $websiteId, $ruleData['excluded_websites'])) {
                continue;
            }

            $rows[] = [
                'rule_id' => $ruleData['rule_id'],
                'from_time' => $websiteTimeData['from_time'],
                'to_time' => $websiteTimeData['to_time'],
                'website_id' => $websiteId,
                'customer_group_id' => $customerGroupId,
                'product_id' => $productId,
                'action_operator' => $ruleData['action_operator'],
                'action_amount' => $ruleData['action_amount'],
                'action_stop' => $ruleData['action_stop'],
                'sort_order' => $ruleData['sort_order'],
            ];

            if (count($rows) === $batchCount) {
                $connection->insertMultiple($indexTable, $rows);
                $rows = [];
            }
        }
        return $rows;
    }

    /**
     * Check if website is excluded for customer group
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @param array $excludedWebsites
     * @return bool
     */
    private function isWebsiteExcluded(int $customerGroupId, int $websiteId, array $excludedWebsites): bool
    {
        return array_key_exists($customerGroupId, $excludedWebsites)
            && in_array($websiteId, array_values($excludedWebsites[$customerGroupId]), true);
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

<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;
use Zend_Db_Statement_Interface;

class ReindexRuleProductsPriceProcessor
{

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductPriceCalculator $productPriceCalculator
     * @param RuleProductPricesPersistor $pricesPersistor
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductPriceCalculator $productPriceCalculator,
        private readonly RuleProductPricesPersistor $pricesPersistor,
        private readonly TimezoneInterface $localeDate
    ) {
    }

    /**
     * Calculate and save prices for selected product catalog rule records
     *
     * @param Zend_Db_Statement_Interface $productsStmt
     * @param WebsiteInterface $website
     * @param int $batchCount
     * @param bool $useAdditionalTable
     * @param bool $useWebsiteTimezone
     * @return void
     * @throws Zend_Db_Statement_Exception
     */
    public function execute(
        Zend_Db_Statement_Interface $productsStmt,
        WebsiteInterface $website,
        int $batchCount,
        bool $useAdditionalTable,
        bool $useWebsiteTimezone
    ): void {
        $dayPrices = [];
        $stopFlags = [];
        $prevKey = null;

        $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
        $dateInterval = $useWebsiteTimezone
            ? $this->getDateInterval((int)$storeGroup->getDefaultStoreId())
            : $this->getDateInterval(Store::DEFAULT_STORE_ID);

        while ($ruleData = $productsStmt->fetch()) {
            $ruleProductId = (int)$ruleData['product_id'];
            $productKey = $ruleProductId .
                '_' .
                $ruleData['website_id'] .
                '_' .
                $ruleData['customer_group_id'];

            if ($prevKey && $prevKey != $productKey) {
                $stopFlags = [];
                if (count($dayPrices) > $batchCount) {
                    $this->pricesPersistor->execute($dayPrices, $useAdditionalTable);
                    $dayPrices = [];
                }
            }

            /**
             * Build prices for each day
             */
            foreach ($dateInterval as $date) {
                $this->processDate(
                    $ruleData,
                    $date,
                    $productKey,
                    $ruleProductId,
                    $dayPrices,
                    $stopFlags
                );
            }

            $prevKey = $productKey;
        }
        $this->pricesPersistor->execute($dayPrices, $useAdditionalTable);
    }

    /**
     * Calculate prices for the given date
     *
     * @param array $ruleData
     * @param DateTime $date
     * @param string $productKey
     * @param int $ruleProductId
     * @param array $dayPrices
     * @param array $stopFlags
     * @return void
     */
    private function processDate(
        array $ruleData,
        DateTime $date,
        string $productKey,
        int $ruleProductId,
        array &$dayPrices,
        array &$stopFlags
    ): void {
        $time = $date->getTimestamp();
        if (($ruleData['from_time'] == 0 ||
                $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
                $time <= $ruleData['to_time'])
        ) {
            $priceKey = $time . '_' . $productKey;

            if (isset($stopFlags[$priceKey])) {
                return;
            }

            if (!isset($dayPrices[$priceKey])) {
                $dayPrices[$priceKey] = [
                    'rule_date' => $date,
                    'website_id' => $ruleData['website_id'],
                    'customer_group_id' => $ruleData['customer_group_id'],
                    'product_id' => $ruleProductId,
                    'rule_price' => $this->productPriceCalculator->calculate($ruleData),
                    'latest_start_date' => $ruleData['from_time'],
                    'earliest_end_date' => $ruleData['to_time'],
                ];
            } else {
                $dayPrices[$priceKey]['rule_price'] = $this->productPriceCalculator->calculate(
                    $ruleData,
                    $dayPrices[$priceKey]
                );
                $dayPrices[$priceKey]['latest_start_date'] = max(
                    $dayPrices[$priceKey]['latest_start_date'],
                    $ruleData['from_time']
                );
                $dayPrices[$priceKey]['earliest_end_date'] = min(
                    $dayPrices[$priceKey]['earliest_end_date'],
                    $ruleData['to_time']
                );
            }

            if ($ruleData['action_stop']) {
                $stopFlags[$priceKey] = true;
            }
        }
    }

    /**
     * Retrieve date sequence in store time zone
     *
     * @param int $storeId
     * @return DateTime[]
     */
    private function getDateInterval(int $storeId): array
    {
        $currentDate = $this->localeDate->scopeDate($storeId, null, true);
        $previousDate = (clone $currentDate)->modify('-1 day');
        $previousDate->setTime(23, 59, 59);
        $nextDate = (clone $currentDate)->modify('+1 day');
        $nextDate->setTime(0, 0, 0);

        return [$previousDate, $currentDate, $nextDate];
    }
}

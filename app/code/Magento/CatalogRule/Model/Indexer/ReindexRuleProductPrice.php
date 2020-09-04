<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Reindex product prices according rule settings.
 */
class ReindexRuleProductPrice
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RuleProductsSelectBuilder
     */
    private $ruleProductsSelectBuilder;

    /**
     * @var ProductPriceCalculator
     */
    private $productPriceCalculator;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var RuleProductPricesPersistor
     */
    private $pricesPersistor;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param ProductPriceCalculator $productPriceCalculator
     * @param TimezoneInterface $localeDate
     * @param RuleProductPricesPersistor $pricesPersistor
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        ProductPriceCalculator $productPriceCalculator,
        TimezoneInterface $localeDate,
        RuleProductPricesPersistor $pricesPersistor
    ) {
        $this->storeManager = $storeManager;
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->localeDate = $localeDate;
        $this->pricesPersistor = $pricesPersistor;
    }

    /**
     * Reindex product prices.
     *
     * @param int $batchCount
     * @param int|null $productId
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(int $batchCount, ?int $productId = null, bool $useAdditionalTable = false)
    {
        /**
         * Update products rules prices per each website separately
         * because for each website date in website's timezone should be used
         */
        foreach ($this->storeManager->getWebsites() as $website) {
            $productsStmt = $this->ruleProductsSelectBuilder->build($website->getId(), $productId, $useAdditionalTable);
            $dayPrices = [];
            $stopFlags = [];
            $prevKey = null;

            $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
            $currentDate = $this->localeDate->scopeDate($storeGroup->getDefaultStoreId(), null, true);
            $previousDate = (clone $currentDate)->modify('-1 day');
            $previousDate->setTime(23, 59, 59);
            $nextDate = (clone $currentDate)->modify('+1 day');
            $nextDate->setTime(0, 0, 0);

            while ($ruleData = $productsStmt->fetch()) {
                $ruleProductId = $ruleData['product_id'];
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
                foreach ([$previousDate, $currentDate, $nextDate] as $date) {
                    $time = $date->getTimestamp();
                    if (($ruleData['from_time'] == 0 ||
                            $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
                            $time <= $ruleData['to_time'])
                    ) {
                        $priceKey = $time . '_' . $productKey;

                        if (isset($stopFlags[$priceKey])) {
                            continue;
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

                $prevKey = $productKey;
            }
            $this->pricesPersistor->execute($dayPrices, $useAdditionalTable);
        }

        return true;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

/**
 * Reindex product prices according rule settings.
 */
class ReindexRuleProductPrice
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder
     */
    private $ruleProductsSelectBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator
     */
    private $productPriceCalculator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor
     */
    private $pricesPersistor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param ProductPriceCalculator $productPriceCalculator
     * @param @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor $pricesPersistor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator $productPriceCalculator,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor $pricesPersistor
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
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(
        $batchCount,
        \Magento\Catalog\Model\Product $product = null,
        $useAdditionalTable = false
    ) {
        /**
         * Update products rules prices per each website separately
         * because for each website date in website's timezone should be used
         */
        foreach ($this->storeManager->getWebsites() as $website) {
            $productsStmt = $this->ruleProductsSelectBuilder->build($website->getId(), $product, $useAdditionalTable);
            $dayPrices = [];
            $stopFlags = [];
            $prevKey = null;

            $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
            $currentDate = $this->localeDate->scopeDate($storeGroup->getDefaultStoreId());
            $previousDate = (clone $currentDate)->modify('-1 day');
            $nextDate = (clone $currentDate)->modify('+1 day');

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

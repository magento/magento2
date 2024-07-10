<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;

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
     * @var bool
     */
    private $useWebsiteTimezone;

    /**
     * @var ReindexRuleProductsPriceProcessor
     */
    private $reindexRuleProductsPriceProcessor;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param ProductPriceCalculator $productPriceCalculator
     * @param TimezoneInterface $localeDate
     * @param RuleProductPricesPersistor $pricesPersistor
     * @param bool $useWebsiteTimezone
     * @param ReindexRuleProductsPriceProcessor|null $reindexRuleProductsPriceProcessor
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        ProductPriceCalculator $productPriceCalculator,
        TimezoneInterface $localeDate,
        RuleProductPricesPersistor $pricesPersistor,
        bool $useWebsiteTimezone = true,
        ReindexRuleProductsPriceProcessor $reindexRuleProductsPriceProcessor = null
    ) {
        $this->storeManager = $storeManager;
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->localeDate = $localeDate;
        $this->pricesPersistor = $pricesPersistor;
        $this->useWebsiteTimezone = $useWebsiteTimezone;
        $this->reindexRuleProductsPriceProcessor = $reindexRuleProductsPriceProcessor ??
            ObjectManager::getInstance()->get(ReindexRuleProductsPriceProcessor::class);
    }

    /**
     * Reindex product prices.
     *
     * @param int $batchCount
     * @param int|null $productId
     * @param bool $useAdditionalTable
     * @return bool
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(int $batchCount, ?int $productId = null, bool $useAdditionalTable = false)
    {
        /**
         * Update products rules prices per each website separately
         * because for each website date in website's timezone should be used
         */
        foreach ($this->storeManager->getWebsites() as $website) {
            $productsStmt = $this->ruleProductsSelectBuilder->build(
                (int)$website->getId(),
                $productId,
                $useAdditionalTable
            );
            $this->reindexRuleProductsPriceProcessor->execute(
                $productsStmt,
                $website,
                $batchCount,
                $useAdditionalTable,
                $this->useWebsiteTimezone
            );
        }

        return true;
    }
}

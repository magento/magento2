<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;

/**
 * Reindex product prices according rule settings.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var RuleProductsSelectBatchProvider
     */
    private $batchProvider;

    /**
     * @param StoreManagerInterface $storeManager
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param ProductPriceCalculator $productPriceCalculator
     * @param TimezoneInterface $localeDate
     * @param RuleProductPricesPersistor $pricesPersistor
     * @param bool $useWebsiteTimezone
     * @param ReindexRuleProductsPriceProcessor|null $reindexRuleProductsPriceProcessor
     * @param RuleProductsSelectBatchProvider|null $batchProvider
     * @param bool $useSelectBatching Whether to batch the rule-product select for memory efficiency.
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        ProductPriceCalculator $productPriceCalculator,
        TimezoneInterface $localeDate,
        RuleProductPricesPersistor $pricesPersistor,
        bool $useWebsiteTimezone = true,
        ?ReindexRuleProductsPriceProcessor $reindexRuleProductsPriceProcessor = null,
        ?RuleProductsSelectBatchProvider $batchProvider = null,
        private readonly bool $useSelectBatching = true
    ) {
        $this->storeManager = $storeManager;
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->localeDate = $localeDate;
        $this->pricesPersistor = $pricesPersistor;
        $this->useWebsiteTimezone = $useWebsiteTimezone;
        $this->reindexRuleProductsPriceProcessor = $reindexRuleProductsPriceProcessor ??
            ObjectManager::getInstance()->get(ReindexRuleProductsPriceProcessor::class);
        $this->batchProvider = $batchProvider
            ?? ObjectManager::getInstance()->get(RuleProductsSelectBatchProvider::class);
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
        if ($this->useSelectBatching === true && $productId === null && $useAdditionalTable) {
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->reindexRuleProductsPriceProcessor->executeBatches(
                    $this->batchProvider->execute((int)$website->getId(), null, true),
                    $website,
                    $batchCount,
                    true,
                    $this->useWebsiteTimezone
                );
            }
            return true;
        }

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

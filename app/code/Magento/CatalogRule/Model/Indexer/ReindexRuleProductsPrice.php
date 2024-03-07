<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Statement_Exception;

/**
 * Reindex product prices according rule settings.
 */
class ReindexRuleProductsPrice
{

    /**
     * @param StoreManagerInterface $storeManager
     * @param ReindexRuleProductsPriceProcessor $reindexRuleProductsPriceProcessor
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param bool $useWebsiteTimezone
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ReindexRuleProductsPriceProcessor $reindexRuleProductsPriceProcessor,
        private readonly RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        private readonly bool $useWebsiteTimezone = true
    ) {
    }

    /**
     * Reindex products prices.
     *
     * @param int $batchCount
     * @param array $productIds
     * @param bool $useAdditionalTable
     * @return bool
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(int $batchCount, array $productIds, bool $useAdditionalTable = false): bool
    {
        /**
         * Update products rules prices per each website separately
         * because for each website date in website's timezone should be used
         */
        foreach ($this->storeManager->getWebsites() as $website) {
            $productsStmt = $this->ruleProductsSelectBuilder->buildSelect(
                (int)$website->getId(),
                $productIds,
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

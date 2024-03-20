<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Indexer\IndexerRegistry;

class RuleProductProcessor extends AbstractProcessor
{
    /**
     * Indexer id
     */
    public const INDEXER_ID = 'catalogrule_rule';

    /**
     * @var ProductRuleProcessor
     */
    private $productRuleProcessor;

    /**
     * @var ProductPriceProcessor
     */
    private $productPriceProcessor;

    /**
     * @var GetAffectedProductIds
     */
    private $getAffectedProductIds;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ProductRuleProcessor|null $productRuleProcessor
     * @param ProductPriceProcessor|null $productPriceProcessor
     * @param GetAffectedProductIds|null $getAffectedProductIds
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        ?ProductRuleProcessor $productRuleProcessor = null,
        ?ProductPriceProcessor $productPriceProcessor = null,
        ?GetAffectedProductIds $getAffectedProductIds = null
    ) {
        $this->productRuleProcessor = $productRuleProcessor
            ?? ObjectManager::getInstance()->get(ProductRuleProcessor::class);
        $this->productPriceProcessor = $productPriceProcessor
            ?? ObjectManager::getInstance()->get(ProductPriceProcessor::class);
        $this->getAffectedProductIds = $getAffectedProductIds
            ?? ObjectManager::getInstance()->get(GetAffectedProductIds::class);
        parent::__construct($indexerRegistry);
    }

    /**
     * @inheritdoc
     */
    public function reindexRow($id, $forceReindex = false)
    {
        $this->reindexList([$id], $forceReindex);
    }

    /**
     * @inheritdoc
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (empty($ids) || !$forceReindex && $this->isIndexerScheduled()) {
            return;
        }
        $affectedProductIds = $this->getAffectedProductIds->execute($ids);
        if (!$affectedProductIds) {
            return;
        }
        // catalog_product_price depends on catalogrule_rule. However, their interfaces are not compatible,
        // thus the rule is indexed using catalogrule_product
        // and price indexer is triggered to update dependent indexes.
        $this->productRuleProcessor->reindexList($affectedProductIds);
        $this->productPriceProcessor->reindexList($affectedProductIds);
    }
}

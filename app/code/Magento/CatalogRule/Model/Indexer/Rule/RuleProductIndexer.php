<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\CatalogRule\Model\Indexer\AbstractIndexer;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;

class RuleProductIndexer extends AbstractIndexer
{
    /**
     * @var ProductRuleProcessor
     */
    private $productRuleProcessor;

    /**
     * @var GetAffectedProductIds
     */
    private $getAffectedProductIds;

    /**
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     * @param ProductRuleProcessor|null $productRuleProcessor
     * @param GetAffectedProductIds|null $getAffectedProductIds
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager,
        ?ProductRuleProcessor $productRuleProcessor = null,
        ?GetAffectedProductIds $getAffectedProductIds = null
    ) {
        $this->productRuleProcessor = $productRuleProcessor
            ?? ObjectManager::getInstance()->get(ProductRuleProcessor::class);
        $this->getAffectedProductIds = $getAffectedProductIds
            ?? ObjectManager::getInstance()->get(GetAffectedProductIds::class);
        parent::__construct($indexBuilder, $eventManager);
    }

    /**
     * @inheritdoc
     */
    protected function doExecuteList($ids)
    {
        $affectedProductIds = $this->getAffectedProductIds->execute($ids);
        if (!$affectedProductIds) {
            return;
        }
        $this->productRuleProcessor->reindexList($affectedProductIds, true);
    }

    /**
     * @inheritdoc
     */
    protected function doExecuteRow($id)
    {
        $this->doExecuteList([$id]);
    }
}

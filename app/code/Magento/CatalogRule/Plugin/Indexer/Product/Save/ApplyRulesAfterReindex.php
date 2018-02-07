<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product\Save;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

class ApplyRulesAfterReindex
{
    /**
     * @var ProductRuleProcessor
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply catalog rules after product resource model save
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @return \Magento\Catalog\Model\Product
     */
    public function aroundReindex(
        \Magento\Catalog\Model\Product $subject,
        callable $proceed
    ) {
        $proceed();
        $this->productRuleProcessor->reindexRow($subject->getId());
        return;
    }
}

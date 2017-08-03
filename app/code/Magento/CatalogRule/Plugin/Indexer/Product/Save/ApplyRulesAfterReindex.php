<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product\Save;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Catalog\Model\Product;

/**
 * Plugin for Magento\Catalog\Model\Product
 * @since 2.0.1
 */
class ApplyRulesAfterReindex
{
    /**
     * @var ProductRuleProcessor
     * @since 2.0.1
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     * @since 2.0.1
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply catalog rules after product resource model save
     *
     * @param Product $subject
     * @return void
     * @since 2.2.0
     */
    public function afterReindex(Product $subject)
    {
        $this->productRuleProcessor->reindexRow($subject->getId());
    }
}

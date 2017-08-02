<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product\Save;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Class \Magento\CatalogRule\Plugin\Indexer\Product\Save\ApplyRules
 *
 * @since 2.0.0
 */
class ApplyRules
{
    /**
     * @var ProductRuleProcessor
     * @since 2.0.0
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     * @since 2.0.0
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply catalog rules after product resource model save
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        if (!$product->getIsMassupdate()) {
            $this->productRuleProcessor->reindexRow($product->getId());
        }
        return $productResource;
    }
}

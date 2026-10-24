<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product\Save;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;

class ApplyRules
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
     * Apply catalog rules after product resource model save using commit callback
     *
     * This ensures indexing happens after transaction commit, avoiding DDL transaction issues.
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundSave(
        ResourceProduct $productResource,
        \Closure $proceed,
        AbstractModel $product
    ) {
        // Check if we're already in a transaction (e.g., from CatalogSearch plugin)
        $isInTransaction = $productResource->getConnection()->getTransactionLevel() > 0;

        if ($isInTransaction) {
            // If already in a transaction, register the commit callback BEFORE proceed(),
            // so it executes on the current commit of the wrapped save().
            if (!$product->getIsMassupdate()) {
                $productResource->addCommitCallback(function () use ($product) {
                    // Force reindex to ensure prices are written to catalogrule_product_price
                    $this->productRuleProcessor->reindexRow($product->getId());
                });
            }
            return $proceed($product);
        } else {
            // If not in transaction, wrap in transaction like CatalogSearch does
            return $this->addCommitCallback($productResource, $proceed, $product);
        }
    }

    /**
     * Apply catalog rules after product resource model delete using commit callback
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundDelete(
        ResourceProduct $productResource,
        \Closure $proceed,
        AbstractModel $product
    ) {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * Wrap save/delete in transaction and add commit callback for indexing
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    private function addCommitCallback(
        ResourceProduct $productResource,
        \Closure $proceed,
        AbstractModel $product
    ) {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            if (!$product->getIsMassupdate()) {
                $productResource->addCommitCallback(function () use ($product) {
                    // Force reindex to ensure prices are written to catalogrule_product_price
                    $this->productRuleProcessor->reindexRow($product->getId());
                });
            }
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;

/**
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class Product extends AbstractPlugin
{
    /**
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundSave(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * Reindex on product delete
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundDelete(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    private function addCommitCallback(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            $productResource->addCommitCallback(function () use ($product) {
                $this->reindexRow($product->getEntityId());
            });
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }
}

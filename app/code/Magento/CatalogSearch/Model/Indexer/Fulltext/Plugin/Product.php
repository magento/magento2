<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

class Product extends AbstractPlugin
{
    /**
     * Reindex on product save
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave(\Magento\Catalog\Model\Product $product)
    {
        $this->reindexRow($product->getId());
        return $product;
    }

    /**
     * Reindex on product delete
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterDelete(\Magento\Catalog\Model\Product $product)
    {
        $this->reindexRow($product->getId());
        return $product;
    }
}

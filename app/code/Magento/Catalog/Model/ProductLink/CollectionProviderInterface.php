<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\ProductLink;

/**
 * Interface \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
 *
 * @api
 */
interface CollectionProviderInterface
{
    /**
     * Get linked products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product);
}

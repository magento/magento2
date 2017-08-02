<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink;

/**
 * Interface \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
 *
 * @since 2.0.0
 */
interface CollectionProviderInterface
{
    /**
     * Get linked products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     * @since 2.0.0
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product);
}

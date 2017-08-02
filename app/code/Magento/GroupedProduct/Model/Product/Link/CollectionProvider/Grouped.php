<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\Product\Link\CollectionProvider;

/**
 * Class \Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped
 *
 * @since 2.0.0
 */
class Grouped implements \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product)
    {
        return $product->getTypeInstance()->getAssociatedProducts($product);
    }
}

<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Plugin\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Plugin for Product Identity
 */
class Product
{
    /**
     * Add identity of child product to identities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string[] $result
     * @return string[]
     */
    public function afterGetIdentities(\Magento\Catalog\Model\Product $product, $result)
    {
        /** @var Configurable $productType */
        $productType = $product->getTypeInstance();
        if ($productType instanceof Configurable) {
            foreach ($productType->getChildrenIds($product->getId())[0] as $productId) {
                $result[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $productId;
            }
        }
        return $result;
    }
}

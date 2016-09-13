<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Plugin\Model;

/**
 * Plugin for Product Identity
 */
class Product
{
    /**
     *  Configurable product type resource
     *
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $catalogProductTypeConfigurable;

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
    ) {
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
    }

    /**
     * Add identity of parent product to identities of configurable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string[] $result
     * @return string[]
     */
    public function afterGetIdentities(\Magento\Catalog\Model\Product $product, $result)
    {
        foreach ($this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId()) as $parentId) {
            $result[] = \Magento\Catalog\Model\Product::CACHE_TAG . '_' . $parentId;
        }

        return $result;
    }
}

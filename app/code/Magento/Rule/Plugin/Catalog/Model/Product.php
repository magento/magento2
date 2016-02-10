<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Caching result for avaliable categories in product
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rule\Plugin\Catalog\Model;

class Product
{
    /**
     * @var array
     */
    protected $productCategoryIds = [];

    /**
     * Apply catalog rules after product resource model save
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param callable $proceed
     * @return array
     */
    public function aroundGetAvailableInCategories(\Magento\Catalog\Model\Product $product, callable $proceed)
    {
        $entityId = $product->getEntityId();
        if (!isset($this->productCategoryIds[$entityId])) {
            $this->productCategoryIds[$entityId] = $proceed();
        }
        return $this->productCategoryIds[$entityId];
    }
}

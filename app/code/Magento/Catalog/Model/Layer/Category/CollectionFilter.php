<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\CollectionFilterInterface;

class CollectionFilter implements CollectionFilterInterface
{
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * CollectionFilter constructor
     *
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $catalogConfig
    ) {
        $this->productVisibility = $productVisibility;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Filter product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    public function filter(
        $collection,
        \Magento\Catalog\Model\Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId())
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Swatches\Test\Block\Product\ProductList\ProductItem;
use Magento\Swatches\Test\Block\Product\ListProduct;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Swatches\Test\Fixture\ConfigurableProduct;

/**
 * Configure swatch variation of configurable product on category page.
 */
class CategoryConfigureProductStep implements TestStepInterface
{
    /**
     * @var CatalogCategoryView
     */
    private $categoryView;

    /**
     * @var ConfigurableProduct
     */
    private $product;

    /**
     * OpenProductInCatalog constructor.
     *
     * @param CatalogCategoryView $categoryView
     * @param ConfigurableProduct $product
     */
    public function __construct(
        CatalogCategoryView $categoryView,
        ConfigurableProduct $product
    ) {
        $this->categoryView = $categoryView;
        $this->product = $product;
    }

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function run()
    {
        /** @var ListProduct $productsList */
        $productsList = $this->categoryView->getBlockInstance('listSwatchesProductBlock');
        /** @var ProductItem $productItemBlock */
        $productItemBlock = $productsList->getProductItem($this->product);
        $productItemBlock->fillData($this->product);
        return ['categoryProductBlock' => $productItemBlock];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Swatches\Test\Block\Product\ProductList\ProductItem;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Add configurable product to cart.
 */
class AddProductToCartFromCatalogCategoryPageStep implements TestStepInterface
{
    /**
     * Fixture of configurable product with swatches configuration.
     *
     * @var \Magento\Swatches\Test\Fixture\ConfigurableProduct
     */
    private $product;

    /**
     * Fixture factory for create/get fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Page of catalog category view.
     *
     * @var CatalogCategoryView
     */
    private $categoryView;

    /**
     * CMS index page.
     *
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param InjectableFixture $product
     * @param CatalogCategoryView $categoryView
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CatalogCategoryView $categoryView,
        InjectableFixture $product
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->cmsIndex = $cmsIndex;
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
        $categoryName = $this->product->getCategoryIds()[0];
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        /** @var  \Magento\Swatches\Test\Block\Product\ListProduct $productsList */
        $productsList = $this->categoryView->getListSwatchesProductBlock();
        /** @var ProductItem $productItemBlock */
        $productItemBlock = $productsList->getProductItem($this->product);
        $productItemBlock->fillData($this->product);
        $productItemBlock->clickAddToCart();
        $cart = [
            'data' => [
                'items' => [
                    'products' => [$this->product]
                ]
            ]
        ];

        return [
            'cart' => $this->fixtureFactory->createByCode('cart', $cart)
        ];
    }
}

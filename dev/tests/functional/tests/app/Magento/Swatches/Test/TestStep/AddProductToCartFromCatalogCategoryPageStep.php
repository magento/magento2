<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Swatches\Test\Block\Product\ListProduct;
use Magento\Swatches\Test\Block\Product\ProductList\ProductItem;
use Magento\Swatches\Test\Fixture\ConfigurableProduct;

/**
 * Add configurable product to cart.
 */
class AddProductToCartFromCatalogCategoryPageStep implements TestStepInterface
{
    /**
     * Fixture of configurable product with swatches configuration.
     *
     * @var ConfigurableProduct
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
     * @param CatalogCategoryView $categoryView
     * @param InjectableFixture $product
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
        /** @var string $categoryName */
        $categoryName = $this->product->getCategoryIds()[0];

        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);

        /** @var  ListProduct $productsList */
        $productsList = $this->categoryView->getListSwatchesProductBlock();

        /** @var ProductItem $productItemBlock */
        $productItemBlock = $productsList->getProductItem($this->product);
        $productItemBlock->fillData($this->product);
        $productItemBlock->clickAddToCart();
        $cart = [
            'data' => [
                'items' => [
                    'products' => [$this->product],
                ],
            ],
        ];

        return [
            'cart' => $this->fixtureFactory->createByCode('cart', $cart),
        ];
    }
}

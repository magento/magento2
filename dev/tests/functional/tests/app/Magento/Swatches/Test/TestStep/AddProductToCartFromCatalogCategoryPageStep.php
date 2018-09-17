<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Flag wait success added product to cart message or not.
     *
     * @var bool
     */
    private $waitSuccessMessage;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $categoryView
     * @param InjectableFixture $product
     * @param bool $waitSuccessMessage
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CatalogCategoryView $categoryView,
        InjectableFixture $product,
        $waitSuccessMessage = true
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->cmsIndex = $cmsIndex;
        $this->categoryView = $categoryView;
        $this->product = $product;
        $this->waitSuccessMessage = $waitSuccessMessage;
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

        if ($this->waitSuccessMessage) {
            $this->categoryView->getMessagesBlock()->waitSuccessMessage();
        }

        return [
            'cart' => $this->fixtureFactory->createByCode('cart', $cart),
        ];
    }
}

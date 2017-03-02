<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;

/**
 * Preconditions:
 * 1. Configure text swatch attribute.
 * 2. Create configurable product with this attribute
 * 3. Open it on catalog page
 * 4. Click on 'Add to Cart' button
 *
 * Steps:
 * 1. Go to Frontend.
 * 2. Open category page with created product
 * 3. Click on 'Add to Cart' button
 * 4. Perform asserts
 *
 * @group Configurable_Product
 * @ZephyrId MAGETWO-59958, MAGETWO-59979
 */
class AddConfigurableProductWithSwatchToShoppingCartTest extends Injectable
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Page of catalog category view.
     *
     * @var CatalogCategoryView
     */
    private $categoryView;

    /**
     * Injection data.
     *
     * @param TestStepFactory $testStepFactory
     * @param CatalogCategoryView $categoryView
     * @return void
     */
    public function __inject(
        TestStepFactory $testStepFactory,
        CatalogCategoryView $categoryView
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->categoryView = $categoryView;
    }

    /**
     * Runs add configurable product with swatches attributes test.
     *
     * @param ConfigurableProduct $product
     * @param bool $addToCart
     * @return array
     */
    public function test(ConfigurableProduct $product, $addToCart)
    {
        $product->persist();
        $cart = $this->testStepFactory->create(
            \Magento\Swatches\Test\TestStep\AddProductToCartFromCatalogCategoryPageStep::class,
            [
                'product' => $product
            ]
        )->run()['cart'];
        if ($addToCart) {
            $this->categoryView->getMessagesBlock()->waitSuccessMessage();
        }

        return ['cart' => $cart];
    }
}

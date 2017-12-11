<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Swatches\Test\TestStep\AddProductToCartFromCatalogCategoryPageStep as AddToCart;
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
     * @return array
     */
    public function test(ConfigurableProduct $product)
    {
        $product->persist();
        $cart = $this->testStepFactory->create(
            AddToCart::class,
            [
                'product' => $product
            ]
        )->run()['cart'];

        return ['cart' => $cart];
    }
}

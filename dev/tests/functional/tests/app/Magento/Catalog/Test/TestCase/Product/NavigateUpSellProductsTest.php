<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Navigate through up-sell products.
 *
 * @ZephirId MAGETWO-1239
 * @group Cross-sells_(MX)
 */
class NavigateUpSellProductsTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */


    /**
     * Run test navigate up-sell products.
     *
     * @param string $products
     * @param string $promotedProducts
     * @param string $steps
     * @param string $assert
     * @return void
     */
    public function test(
        $products,
        $promotedProducts,
        $steps,
        $assert
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'up_sell_products');

        // Steps
        $steps = $this->parseSteps($steps);
        $assert = $this->parseAssert($assert);
        $initialProductName = array_shift($steps);
        $initialProduct = $this->products[$initialProductName];
        $initialAssert = $assert[$initialProductName];

        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->assertUpSellSection($initialAssert);
        foreach ($steps as $productName) {
            $product = $this->products[$productName];
            $productAssert = $assert[$productName];

            $this->catalogProductView->getUpsellBlock()->getProductItem($product)->open();
            if (empty($productAssert)) {
                $this->assertAbsentUpSellSection();
            } else {
                $this->assertUpSellSection($productAssert);
            }
        }
    }

    /**
     * Assert that absent up-sell section.
     *
     * @return void
     */
    protected function assertAbsentUpSellSection()
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $this->catalogProductView->getUpsellBlock()->isVisible(),
            "Up-sell section is present."
        );
    }

    /**
     * Assert that correctly display up-sell section.
     *
     * @param array $promotedProductNames
     * @return void
     */
    protected function assertUpSellSection(array $promotedProductNames)
    {
        $productNames = [];
        $pageProductNames = [];

        foreach ($promotedProductNames as $promotedProductName) {
            $productNames[] = $this->products[$promotedProductName]->getName();
        }
        foreach ($this->catalogProductView->getUpsellBlock()->getProducts() as $productItem) {
            $pageProductNames[] = $productItem->getProductName();
        }

        sort($productNames);
        sort($pageProductNames);
        \PHPUnit_Framework_Assert::assertEquals($productNames, $pageProductNames, 'Wrong display up-sell section.');
    }
}

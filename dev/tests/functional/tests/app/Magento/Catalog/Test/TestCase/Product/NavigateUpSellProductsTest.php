<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

/**
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Navigate through up-sell products.
 *
 * @ZephyrId MAGETWO-12391
 * @group Up-sells_(MX)
 */
class NavigateUpSellProductsTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run test navigate up-sell products.
     *
     * @param string $products
     * @param string $promotedProducts
     * @param string $navigateProductsOrder
     * @param string $productsToVerify
     * @return void
     */
    public function test(
        $products,
        $promotedProducts,
        $navigateProductsOrder,
        $productsToVerify
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'up_sell_products');

        // Initialization
        $navigateProductsOrder = $this->parseNavigateProductsOrder($navigateProductsOrder);
        $productsToVerify = $this->parseProductsToVerify($productsToVerify);
        $initialProductName = array_shift($navigateProductsOrder);
        $initialProduct = $this->products[$initialProductName];
        $initialProductsToVerify = $productsToVerify[$initialProductName];

        // Steps
        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->assertUpSellSection($initialProductsToVerify);
        foreach ($navigateProductsOrder as $productName) {
            $product = $this->products[$productName];
            $productAssert = $productsToVerify[$productName];

            $this->catalogProductView->getUpsellBlock()->getProductItem($product)->open();
            if (empty($productAssert)) {
                $this->assertAbsentUpSellSection();
            } else {
                $this->assertUpSellSection($productAssert);
            }
        }
    }

    /**
     * Assert that up-sell products section is absent.
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
     * Assert that up-sell products section is displayed correctly.
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
        \PHPUnit_Framework_Assert::assertEquals(
            $productNames,
            $pageProductNames,
            'Wrong products are displayed in up-sell section.'
        );
    }
}

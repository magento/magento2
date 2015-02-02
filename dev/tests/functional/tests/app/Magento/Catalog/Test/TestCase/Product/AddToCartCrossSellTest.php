<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Constraint\AssertProductCrossSellSection;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create products.
 * 2. Assign promoted products.
 *
 * Steps:
 * 1. Add initial products to cart.
 * 2. Verify Cross-sell block on checkout page.
 *
 * @ZephyrId MAGETWO-12390
 * @group Cross-sells_(MX)
 */
class AddToCartCrossSellTest extends AbstractProductPromotedProductsTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Frontend page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Checkout page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Assert that correctly display cross-sell section.
     *
     * @var AssertProductCrossSellSection
     */
    protected $assertProductCrossSellSection;

    /**
     * Run test add to cart cross-sell products.
     *
     * @param string $products
     * @param string $promotedProducts
     * @param string $steps
     * @param string $assert
     * @param CmsIndex $cmsIndex
     * @param CheckoutCart $checkoutCart
     * @param AssertProductCrossSellSection $assertProductCrossSellSection
     * @return void
     */
    public function test(
        $products,
        $promotedProducts,
        $steps,
        $assert,
        CmsIndex $cmsIndex,
        CheckoutCart $checkoutCart,
        AssertProductCrossSellSection $assertProductCrossSellSection
    ) {
        // Preconditions
        $this->createProducts($products);
        $this->assignPromotedProducts($promotedProducts, 'cross_sell_products');

        // Steps
        $this->cmsIndex = $cmsIndex;
        $this->checkoutCart = $checkoutCart;
        $this->assertProductCrossSellSection = $assertProductCrossSellSection;
        $steps = $this->parseSteps($steps);
        $assert = $this->parseAssert($assert);
        $initialProductName = array_shift($steps);
        $initialProduct = $this->products[$initialProductName];
        $initialAssert = $assert[$initialProductName];

        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        $this->browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($initialProduct);
        $this->assertCrossSellSection($initialAssert);
        foreach ($steps as $productName) {
            $this->addToCart($this->products[$productName]);
            $this->assertCrossSellSection($assert[$productName]);
        }
    }

    /**
     * Add product to cart from Cross-sell section.
     *
     * @param InjectableFixture $product
     * @return void
     */
    protected function addToCart(InjectableFixture $product)
    {
        $this->checkoutCart->open();

        $productItem = $this->checkoutCart->getCrosssellBlock()->getProductItem($product);
        \PHPUnit_Framework_Assert::assertTrue(
            $productItem->isVisible(),
            "Product {$product->getName()} is absent in cross-sell block."
        );

        $productItem->clickAddToCart();
        if ($this->cmsIndex->getTitleBlock()->getTitle() == $product->getName()) {
            $this->catalogProductView->getViewBlock()->addToCart($product);
        }
        $this->checkoutCart->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Assert that correctly display cross-sell section.
     *
     * @param array $promotedProductNames
     * @return void
     */
    protected function assertCrossSellSection(array $promotedProductNames)
    {
        $promotedProducts = [];

        foreach ($promotedProductNames as $promotedProductName) {
            $promotedProducts[] = $this->products[$promotedProductName];
        }

        $this->assertProductCrossSellSection->processAssert($this->checkoutCart, $promotedProducts);
    }
}

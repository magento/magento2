<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\Client\Browser;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Update ShoppingCart
 *
 * Test Flow:
 * Precondition:
 * 1. Simple product is created
 * 2. Clear shopping cart
 *
 * Steps:
 * 1. Go to frontend
 * 2. Add product with qty = 1 to shopping cart
 * 3. Fill in all data according to data set
 * 4. Click "Update Shopping Cart" button
 * 5. Perform all assertion from dataset
 *
 * @group Shopping_Cart_(CS)
 * @ZephyrId MAGETWO-25081
 */
class UpdateShoppingCartTest extends Injectable
{
    /**
     * Browser interface
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Page CatalogProductView
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Page CheckoutCart
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Prepare test data
     *
     * @param Browser $browser
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(Browser $browser, FixtureFactory $fixtureFactory)
    {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject data
     *
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function __inject(
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Update Shopping Cart
     *
     * @param CatalogProductSimple $product
     * @return array
     */
    public function test(CatalogProductSimple $product)
    {
        // Preconditions
        $product->persist();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        // Steps
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $productView = $this->catalogProductView->getViewBlock();
        $productView->fillOptions($product);
        $productView->setQty(1);
        $productView->clickAddToCart();

        $qty = $product->getCheckoutData()['qty'];
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->getCartItem($product)->setQty($qty);
        $this->checkoutCart->getCartBlock()->updateShoppingCart();

        $cart['data']['items'] = ['products' => [$product]];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }
}

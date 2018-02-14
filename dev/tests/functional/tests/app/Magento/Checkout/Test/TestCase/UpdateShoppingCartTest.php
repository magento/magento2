<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
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
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Browser interface
     *
     * @var BrowserInterface
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
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(BrowserInterface $browser, FixtureFactory $fixtureFactory)
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
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();

        // Steps
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $productView = $this->catalogProductView->getViewBlock();
        $productView->fillOptions($product);
        $productView->setQty($product->getCheckoutData()['qty']);
        $productView->clickAddToCart();
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();

        $qty = $product->getCheckoutData()['qty'];
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->getCartItem($product)->setQty($qty);
        $this->checkoutCart->getCartBlock()->updateShoppingCart();

        $cart['data']['items'] = ['products' => [$product]];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }
}

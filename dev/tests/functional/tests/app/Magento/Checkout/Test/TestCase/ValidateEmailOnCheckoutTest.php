<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Simple product is created
 * 2. Clear shopping cart
 *
 * Steps:
 * 1. Go to Storefront as Guest
 * 2. Add simple product to shopping cart
 * 3. Go to Checkout
 * 4. Enter the email according to the data set
 * 5. Perform assertions
 *
 * @ZephyrId MAGETWO-42543
 */
class ValidateEmailOnCheckoutTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Validate email on checkout.
     *
     * @param CatalogProductSimple $product
     * @param CheckoutCart $cartPage
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @return void
     */
    public function test(
        CatalogProductSimple $product,
        CheckoutCart $cartPage,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        CheckoutOnepage $checkoutOnepage,
        Customer $customer
    ) {
        //Preconditions
        $product->persist();

        $cartPage->open();
        $cartPage->getCartBlock()->clearShoppingCart();

        //Steps
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $productView = $catalogProductView->getViewBlock();
        $productView->fillOptions($product);
        $productView->setQty($product->getCheckoutData()['qty']);
        $productView->clickAddToCart();
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();

        $checkoutOnepage->open();
        $checkoutOnepage->getShippingBlock()->fill($customer);
        $checkoutOnepage->getShippingMethodBlock()->clickContinue();
    }
}

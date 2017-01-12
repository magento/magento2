<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that checks if min qty setting is working correctly.
 */
class AssertProductInventoryMinAllowedQty extends AbstractConstraint
{
    /**
     * Error message text.
     *
     * @var string
     */
    private $errorMessage = 'The fewest you may purchase is %s.';

    /**
     * Check if min qty setting is working correctly.
     *
     * @param BrowserInterface $browser
     * @param FixtureInterface $product
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param int $minQty
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        FixtureInterface $product,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        $minQty
    ) {
        // Ensure that shopping cart is empty
        $checkoutCart->open()->getCartBlock()->clearShoppingCart();

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->waitLoader();
        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart(1);
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogProductView->getMessagesBlock()->getErrorMessage(),
            sprintf($this->errorMessage, $minQty),
            'Limiting min qty is not working correctly.'
        );

        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart($minQty);
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductView->getMessagesBlock()->waitSuccessMessage(),
            'Limiting min qty is not working correctly.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Limiting min qty is working correctly.';
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that checks if max qty setting is working correctly.
 */
class AssertProductInventoryMaxAllowedQty extends AbstractConstraint
{
    /**
     * Error message text.
     *
     * @var string
     */
    private $errorMessage = 'The maximum you may purchase is %s.';

    /**
     * Check if max qty setting is working correctly.
     *
     * @param BrowserInterface $browser
     * @param FixtureInterface $product
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param int $maxQty
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        FixtureInterface $product,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        $maxQty
    ) {
        // Ensure that shopping cart is empty
        $checkoutCart->open()->getCartBlock()->clearShoppingCart();

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->waitLoader();
        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart($maxQty * 2);
        \PHPUnit\Framework\Assert::assertEquals(
            sprintf($this->errorMessage, $maxQty),
            $catalogProductView->getViewBlock()->getQtyErrorMessage(),
            'The maximum purchase warning message is not appears.'
        );

        $catalogProductView->getViewBlock()->setQtyAndClickAddToCart($maxQty);
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getMessagesBlock()->waitSuccessMessage(),
            'Limiting max qty is not working correctly.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Limiting max qty is working correctly.';
    }
}

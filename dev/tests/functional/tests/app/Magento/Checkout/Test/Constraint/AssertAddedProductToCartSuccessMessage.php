<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertAddedProductToCartSuccessMessage
 * Assert success message is appeared on Shopping Cart page
 */
class AssertAddedProductToCartSuccessMessage extends AbstractConstraint
{
    /**
     * Success add to cart message
     */
    const SUCCESS_MESSAGE = 'You added %s to your shopping cart.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert success message is appeared on Shopping Cart page
     *
     * @param CheckoutCart $checkoutCart
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, FixtureInterface $product)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $product->getName()),
            $checkoutCart->getMessagesBlock()->getSuccessMessages()
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Add to cart success message is present on Shopping Cart page.';
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert products is absent in Wishlist on Frontend.
 */
class AssertProductsIsAbsentInWishlist extends AbstractConstraint
{
    /**
     * Assert that product is not present in Wishlist on Frontend.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture[] $products
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        WishlistIndex $wishlistIndex,
        $products,
        Customer $customer
    ) {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $customerAccountIndex->open()->getAccountMenuBlock()->openMenuItem("My Wish List");
        $itemBlock = $wishlistIndex->getWishlistBlock()->getProductItemsBlock();

        foreach ($products as $itemProduct) {
            \PHPUnit\Framework\Assert::assertFalse(
                $itemBlock->getItemProduct($itemProduct)->isVisible(),
                'Product \'' . $itemProduct->getName() . '\' is present in Wish List on Frontend.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is absent in Wish List on Frontend.';
    }
}

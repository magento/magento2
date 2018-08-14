<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CheckoutIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that product is present in grid on customer's shopping cart on backend.
 */
class AssertProductInCustomerShoppingCartOnBackendGrid extends AbstractConstraint
{
    /**
     * Assert that product is present in grid on customer's shopping cart on backend.
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CheckoutIndex $checkoutIndex
     * @param Customer $customer
     * @param array $productsInCart
     * @return void
     */
    public function processAssert(
        CustomerIndexEdit $customerIndexEdit,
        CheckoutIndex $checkoutIndex,
        Customer $customer,
        array $productsInCart
    ) {
        $customerIndexEdit->open(['id' => $customer->getId()]);
        $customerIndexEdit->getPageActionsBlock()->manageShoppingCart();
        foreach ($productsInCart as $product) {
            \PHPUnit_Framework_Assert::assertEquals(
                $product->getName(),
                $checkoutIndex->getItemsBlock()->getItemName($product),
                'Product ' . $product->getName() . " is not present in grid on customer's shopping cart on backend."
            );
        }
    }

    /**
     * Assert success message that product is present in grid on customer's shopping cart on backend.
     *
     * @return string
     */
    public function toString()
    {
        return "Product is present in grid on customer's shopping cart on backend.";
    }
}

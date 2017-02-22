<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\Page\Adminhtml\CheckoutIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that product is present in grid on customer's shopping cart on backend.
 */
class AssertProductInCustomerShoppingCartOnBackendGrid extends AbstractConstraint
{
    /**
     * Assert that product is present in grid on customer's shopping cart on backend.
     *
     * @param BrowserInterface $browser
     * @param CustomerIndexEdit $customerIndexEdit
     * @param CheckoutIndex $checkoutIndex
     * @param Customer $customer
     * @param array $productsInCart
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CustomerIndexEdit $customerIndexEdit,
        CheckoutIndex $checkoutIndex,
        Customer $customer,
        array $productsInCart
    ) {
        $browser->open($_ENV['app_backend_url'] . 'customer/index/edit/id/' . $customer->getId());
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

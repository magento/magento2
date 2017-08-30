<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Address;

/**
 * Assert that Order Billing and Shipping addresses are correct on order page in backend.
 */
class AssertOrderAddresses extends AbstractConstraint
{
    /**
     * Assert that Order Billing and Shipping addresses are correct on order page in backend.
     *
     * @param SalesOrderView $salesOrderView
     * @param string $orderId
     * @param Address $shippingAddress
     * @param Address $billingAddress
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        $orderId,
        Address $shippingAddress,
        Address $billingAddress
    ) {

        $selectedShippingAddress = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $shippingAddress, 'type' => 'html']
        )->render();

        $selectedBillingAddress = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $billingAddress, 'type' => 'html']
        )->render();

        $salesOrderView->open(['order_id' => $orderId]);
        $orderBillingAddress = $salesOrderView->getAddressesBlock()->getCustomerBillingAddress();
        $orderShippingAddress = $salesOrderView->getAddressesBlock()->getCustomerShippingAddress();

        \PHPUnit_Framework_Assert::assertTrue(
            $selectedBillingAddress == $orderBillingAddress && $selectedShippingAddress == $orderShippingAddress,
            'Billing and shipping addresses from the address book and from the order page are not the same.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Billing and shipping addresses from the address book and from the order page are the same.';
    }
}

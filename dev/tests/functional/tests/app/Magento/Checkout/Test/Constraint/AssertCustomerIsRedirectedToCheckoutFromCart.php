<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Sales\Test\Constraint\AssertOrderGrandTotal;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert first step on Checkout page is available.
 * Assert that Order Grand Total is correct on order page in backend.
 */
class AssertCustomerIsRedirectedToCheckoutFromCart extends AbstractConstraint
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Order Id.
     *
     * @var string
     */
    private $orderId;

    /**
     * Assert first step on Checkout page is available.
     * Assert that Order Grand Total is correct on order page in backend.
     *
     * @param CmsIndex $cmsIndex
     * @param CheckoutOnepage $checkoutOnepage
     * @param TestStepFactory $stepFactory
     * @param AssertOrderGrandTotal $assertOrderGrandTotal
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param array $prices
     * @param array $checkoutData
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CheckoutOnepage $checkoutOnepage,
        TestStepFactory $stepFactory,
        AssertOrderGrandTotal $assertOrderGrandTotal,
        SalesOrderView $salesOrderView,
        OrderIndex $orderIndex,
        array $prices,
        array $checkoutData = []
    ) {
        $this->stepFactory = $stepFactory;

        $miniShoppingCart = $cmsIndex->getCartSidebarBlock();
        $miniShoppingCart->openMiniCart();
        $miniShoppingCart->clickProceedToCheckoutButton();

        \PHPUnit_Framework_Assert::assertTrue(
            !$checkoutOnepage->getMessagesBlock()->isVisible()
            && $checkoutOnepage->getShippingMethodBlock()->isVisible(),
            'Checkout first step is not available.'
        );

        if (isset($checkoutData['shippingAddress'])) {
            $this->getOrder($checkoutData);
        }

        //Assert that Order Grand Total is correct on order page in backend.
        $assertOrderGrandTotal->processAssert($salesOrderView, $orderIndex, $this->orderId, $prices);
    }

    /**
     * Get Order.
     *
     * @param array $checkoutData
     * @return void
     */
    protected function getOrder(array $checkoutData)
    {
        $this->stepFactory->create(
            \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
            ['shippingAddress' => $checkoutData['shippingAddress']]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $checkoutData['shipping']]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
            ['payment' => $checkoutData['payment']]
        )->run();
        $this->orderId = $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\PlaceOrderStep::class
        )->run()['orderId'];
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Checkout first step is available.';
    }
}

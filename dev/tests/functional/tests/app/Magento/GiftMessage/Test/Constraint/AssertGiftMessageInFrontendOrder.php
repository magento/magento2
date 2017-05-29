<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that message from dataset is displayed on order(s) view page on frontend.
 */
class AssertGiftMessageInFrontendOrder extends AbstractConstraint
{
    /**
     * Assert that message from dataset is displayed on order(s) view page on frontend.
     *
     * @param GiftMessage $giftMessage
     * @param Customer $customer
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $customerOrderView
     * @param CustomerAccountLogout $customerAccountLogout
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        Customer $customer,
        OrderHistory $orderHistory,
        CustomerOrderView $customerOrderView,
        CustomerAccountLogout $customerAccountLogout,
        $orderId
    ) {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        $expectedData = [
            'sender' => $giftMessage->getSender(),
            'recipient' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedData,
            $customerOrderView->getGiftMessageForOrderBlock()->getGiftMessage(),
            'Wrong gift message is displayed on order.'
        );
        $customerAccountLogout->open();
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return "Gift message is displayed on order(s) view page on frontend correctly.";
    }
}

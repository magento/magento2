<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGiftMessageInFrontendOrder
 * Assert that message from dataSet is displayed on order(s) view page on frontend
 */
class AssertGiftMessageInFrontendOrder extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that message from dataSet is displayed on order(s) view page on frontend
     *
     * @param GiftMessage $giftMessage
     * @param CustomerInjectable $customer
     * @param OrderHistory $orderHistory
     * @param OrderView $orderView
     * @param CustomerAccountLogout $customerAccountLogout
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        CustomerInjectable $customer,
        OrderHistory $orderHistory,
        OrderView $orderView,
        CustomerAccountLogout $customerAccountLogout,
        $orderId
    ) {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
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
            $orderView->getGiftMessageForOrderBlock()->getGiftMessage(),
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

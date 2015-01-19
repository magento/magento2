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
 * Class AssertGiftMessageInFrontendOrderItems
 * Assert that message from dataSet is displayed for each items on order(s) view page on frontend
 */
class AssertGiftMessageInFrontendOrderItems extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that message from dataSet is displayed for each items on order(s) view page on frontend
     *
     * @param GiftMessage $giftMessage
     * @param CustomerInjectable $customer
     * @param OrderHistory $orderHistory
     * @param OrderView $orderView
     * @param CustomerAccountLogout $customerAccountLogout
     * @param string $orderId
     * @param array $products
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        CustomerInjectable $customer,
        OrderHistory $orderHistory,
        OrderView $orderView,
        CustomerAccountLogout $customerAccountLogout,
        $orderId,
        $products = []
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

        foreach ($products as $key => $product) {
            if ($giftMessage->hasData('items')) {
                $itemGiftMessage = $giftMessage->getItems()[$key];
                $expectedData = [
                    'sender' => $itemGiftMessage->getSender(),
                    'recipient' => $itemGiftMessage->getRecipient(),
                    'message' => $itemGiftMessage->getMessage(),
                ];
            }
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedData,
                $orderView->getGiftMessageForItemBlock()->getGiftMessage($product->getName()),
                'Wrong gift message is displayed on "' . $product->getName() . '" item.'
            );
        }
        $customerAccountLogout->open();
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return "Gift message is displayed for each items on order(s) view page on frontend correctly.";
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * Class AssertGiftMessageInFrontendOrderItems
 * Assert that message from dataset is displayed for each items on order(s) view page on frontend
 */
class AssertGiftMessageInFrontendOrderItems extends AbstractConstraint
{
    /**
     * Assert that message from dataset is displayed for each items on order(s) view page on frontend
     *
     * @param GiftMessage $giftMessage
     * @param Customer $customer
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $customerOrderView
     * @param CustomerAccountLogout $customerAccountLogout
     * @param string $orderId
     * @param array $products
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        Customer $customer,
        OrderHistory $orderHistory,
        CustomerOrderView $customerOrderView,
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

        foreach ($giftMessage->getItems() as $key => $itemGiftMessage) {
            $product = $products[$key];
            if ($giftMessage->hasData('items')) {
                $expectedData = [
                    'sender' => $itemGiftMessage->getSender(),
                    'recipient' => $itemGiftMessage->getRecipient(),
                    'message' => $itemGiftMessage->getMessage(),
                ];
            }
            if ($product->getProductHasWeight() !== 'This item has weight') {
                $expectedData = [];
            }

            \PHPUnit_Framework_Assert::assertEquals(
                $expectedData,
                $customerOrderView->getGiftMessageForItemBlock()->getGiftMessage($product->getName()),
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

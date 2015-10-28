<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Constraint;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert that message from dataset is displayed on order(s) view page on backend.
 */
class AssertGiftMessageInBackendOrder extends AbstractAssertForm
{
    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = [
        'allow_gift_options_for_items',
        'allow_gift_messages_for_order',
        'allow_gift_options',
        'items',
    ];

    /**
     * Assert that message from dataset is displayed on order(s) view page on backend.
     *
     * @param GiftMessage $giftMessage
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        SalesOrderView $salesOrderView,
        OrderIndex $orderIndex,
        FixtureFactory $fixtureFactory,
        array $products,
        $orderId
    ) {
        $expectedData = [];
        $actualData = [];
        $orderIndex->open()->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        if ($giftMessage->getAllowGiftMessagesForOrder() === 'Yes') {
            $formData = [
                'sender' => $giftMessage->getSender(),
                'recipient' => $giftMessage->getRecipient(),
                'message' => $giftMessage->getMessage()
            ];
            $giftMessageForm = $fixtureFactory->createByCode('giftMessage', ['data' => $formData]);

            $expectedData[] = $giftMessageForm->getData();
            $actualData[] = $salesOrderView->getGiftOptionsBlock()->getData($giftMessageForm);

        }

        if ($giftMessage->getAllowGiftOptionsForItems() === 'Yes') {
            foreach ($giftMessage->getItems() as $key => $giftMessageItem) {
                $expectedData[] = $giftMessageItem->getData();
                $product = $products[$key];
                $actualData[] = $salesOrderView->getGiftItemsBlock()->getItemProduct($product)
                    ->getGiftMessageFormData($giftMessage);
            }
        }

        $errors = $this->verifyData($expectedData, $actualData);
        \PHPUnit_Framework_Assert::assertEmpty($errors, $errors);
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Backend gift message form data is equal to data passed from dataset.';
    }
}

<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GiftMessage\Test\Constraint;

use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractAssertForm;

/**
 * Class AssertGiftMessageInBackendOrder
 * Assert that message from dataSet is displayed on order(s) view page on backend.
 */
class AssertGiftMessageInBackendOrder extends AbstractAssertForm
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

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
     * Assert that message from dataSet is displayed on order(s) view page on backend.
     *
     * @param GiftMessage $giftMessage
     * @param OrderView $orderView
     * @param OrderIndex $orderIndex
     * @param array $products
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        OrderView $orderView,
        OrderIndex $orderIndex,
        array $products,
        $orderId
    ) {
        $orderIndex->open()->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        if ($giftMessage->getAllowGiftMessagesForOrder()) {
            $expectedData[] = $giftMessage->getData();
            $actualData[] = $orderView->getGiftOptionsBlock()->getData($giftMessage);
        }

        if ($giftMessage->getAllowGiftOptionsForItems()) {
            foreach ($products as $key => $product) {
                $expectedData[] = $giftMessage->getItems()[$key]->getData();
                $actualData[] = $orderView->getGiftItemsBlock()->getItemProduct($product)
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
        return 'Backend gift message form data is equal to data passed from dataSet.';
    }
}

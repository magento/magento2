<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GiftMessage\Test\Constraint;

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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that message from dataSet is displayed for each items on order(s) view page on frontend
     *
     * @param GiftMessage $giftMessage
     * @param OrderHistory $orderHistory
     * @param OrderView $orderView
     * @param string $orderId
     * @param array $products
     * @return void
     */
    public function processAssert(
        GiftMessage $giftMessage,
        OrderHistory $orderHistory,
        OrderView $orderView,
        $orderId,
        $products = []
    ) {
        $expectedData = [
            'sender' => $giftMessage->getSender(),
            'recipient' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];
        $orderHistory->open();
        $orderHistory->getOrderHistoryBlock()->openOrderById($orderId);

        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertEquals(
                $expectedData,
                $orderView->getGiftMessageForItemBlock()->getGiftMessage($product->getName()),
                'Wrong gift message is displayed on "' . $product->getName() . '" item.'
            );
        }
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

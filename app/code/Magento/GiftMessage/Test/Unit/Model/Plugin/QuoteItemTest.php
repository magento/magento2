<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

class QuoteItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Plugin\QuoteItem
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMock(
            \Magento\Sales\Model\Order\Item::class,
            ['setGiftMessageId', 'setGiftMessageAvailable', '__wakeup'],
            [],
            '',
            false
        );
        $this->quoteItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getGiftMessageId', 'getStoreId', '__wakeup'],
            [],
            '',
            false
        );
        $orderItems = $this->orderItemMock;
        $this->closureMock = function () use ($orderItems) {
            return $orderItems;
        };
        $this->subjectMock = $this->getMock(\Magento\Quote\Model\Quote\Item\ToOrderItem::class, [], [], '', false);
        $this->helperMock = $this->getMock(
            \Magento\GiftMessage\Helper\Message::class,
            ['setGiftMessageId', 'isMessagesAllowed'],
            [],
            '',
            false
        );
        $this->model = new \Magento\GiftMessage\Model\Plugin\QuoteItem($this->helperMock);
    }

    public function testAfterItemToOrderItem()
    {
        $storeId = 1;
        $giftMessageId = 1;
        $isMessageAvailable = true;

        $this->quoteItemMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->quoteItemMock->expects(
            $this->any()
        )->method(
            'getGiftMessageId'
        )->will(
            $this->returnValue($giftMessageId)
        );

        $this->helperMock->expects(
            $this->once()
        )->method(
            'isMessagesAllowed'
        )->with(
            'item',
            $this->quoteItemMock,
            $storeId
        )->will(
            $this->returnValue($isMessageAvailable)
        );
        $this->orderItemMock->expects($this->once())->method('setGiftMessageId')->with($giftMessageId);
        $this->orderItemMock->expects($this->once())->method('setGiftMessageAvailable')->with($isMessageAvailable);

        $this->assertSame(
            $this->orderItemMock,
            $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock, [])
        );
    }
}

<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Service\V1;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var string
     */
    protected $cardId;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->cardId = 13;
        $this->quoteRepositoryMock = $this->getMock('\Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->messageFactoryMock = $this->getMock(
            '\Magento\GiftMessage\Model\MessageFactory',
            [
                'create',
                '__wakeup'
            ],
            [],
            '',
            false);
        $this->messageMapperMock = $this->getMock(
            '\Magento\GiftMessage\Service\V1\Data\MessageMapper',
            [
                'extractDto',
                '__wakeup'
            ],
            [],
            '',
            false);
        $this->messageMock = $this->getMock('\Magento\GiftMessage\Model\Message', [], [], '', false);
        $this->quoteItemMock = $this->getMock(
            '\Magento\Sales\Model\Quote\Item',
            [
                'getGiftMessageId',
                '__wakeup'
            ],
            [],
            '',
            false);
        $this->quoteMock = $this->getMock(
            '\Magento\Sales\Model\Quote',
            [
                'getGiftMessageId',
                'getItemById',
                '__wakeup',
            ],
            [],
            '',
            false);

        $this->service = $objectManager->getObject(
            'Magento\GiftMessage\Service\V1\ReadService',
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'messageFactory' => $this->messageFactoryMock,
                'messageMapper' => $this->messageMapperMock,
            ]
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($this->cardId)
            ->will($this->returnValue($this->quoteMock));
    }

    public function testGetWithOutMessageId()
    {
        $messageId = 0;

        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));

        $this->assertNull($this->service->get($this->cardId));
    }

    public function testGet()
    {
        $messageId = 156;

        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));
        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->messageMock));
        $this->messageMock->expects($this->once())->method('load')->will($this->returnValue($this->messageMock));
        $this->messageMapperMock->expects($this->once())
            ->method('extractDto')->with($this->messageMock)->will($this->returnValue(['Expected value']));

        $this->assertEquals(['Expected value'], $this->service->get($this->cardId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no item with provided id in the cart
     */
    public function testGetItemMessageWithNoSuchEntityException()
    {
        $itemId = 2;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));

        $this->service->getItemMessage($this->cardId, $itemId);
    }

    public function testGetItemMessageWithoutMessageId()
    {
        $messageId = 0;
        $itemId = 2;

        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));

        $this->assertNull($this->service->getItemMessage($this->cardId, $itemId));
    }

    public function testGetItemMessage()
    {
        $messageId = 123;
        $itemId = 2;

        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));
        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->messageMock));
        $this->messageMock->expects($this->once())
            ->method('load')
            ->with($messageId)
            ->will($this->returnValue($this->messageMock));
        $this->messageMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->messageMock)
            ->will($this->returnValue(['Expected value']));

        $this->assertEquals(['Expected value'], $this->service->getItemMessage($this->cardId, $itemId));
    }
}

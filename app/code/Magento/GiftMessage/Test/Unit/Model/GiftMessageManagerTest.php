<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\GiftMessageManager;

class GiftMessageManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GiftMessageManager
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteAddressItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $giftMessageMock;

    protected function setUp(): void
    {
        $this->messageFactoryMock =
            $this->createPartialMock(\Magento\GiftMessage\Model\MessageFactory::class, ['create', '__wakeup']);

        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'setGiftMessageId',
                'getGiftMessageId',
                'save',
                'getItemById',
                'getAddressById',
                'getBillingAddress',
                'getShippingAddress',
                '__wakeup',
                'getCustomerId'
            ]
        );
        $this->quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'setGiftMessageId',
                'getGiftMessageId',
                'save',
                '__wakeup'
            ]
        );

        $this->quoteAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getGiftMessageId',
                'setGiftMessageId',
                'getItemById',
                'save',
                '__wakeup'
            ]
        );

        $this->quoteAddressItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Item::class,
            [
                'getGiftMessageId',
                'setGiftMessageId',
                'save',
                '__wakeup'
            ]
        );

        $this->giftMessageMock = $this->createPartialMock(
            \Magento\GiftMessage\Model\Message::class,
            [
                'setSender',
                'setRecipient',
                'setMessage',
                'setCustomerId',
                'getSender',
                'getRecipient',
                'getMessage',
                'getId',
                'delete',
                'save',
                '__wakeup',
                'load'
            ]
        );

        $this->model = new \Magento\GiftMessage\Model\GiftMessageManager($this->messageFactoryMock);
    }

    public function testAddWhenGiftMessagesIsNoArray()
    {
        $giftMessages = '';
        $this->messageFactoryMock->expects($this->never())->method('create');
        $this->model->add($giftMessages, $this->quoteMock);
    }

    public function testAddWithSaveMessageIdAndEmptyMessageException()
    {
        $giftMessages = [
            'quote' => [
                0 => [
                    'from' => 'sender',
                    'to' => 'recipient',
                    'message' => ' ',
                ],
            ],
        ];

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->willReturn(null);
        $this->giftMessageMock->expects($this->never())->method('load');
        $this->giftMessageMock->expects($this->once())->method('getId')->willReturn(1);
        $this->giftMessageMock->expects($this->once())->method('delete');
        $this->quoteMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(0)
            ->willReturn($this->quoteMock);
        $exception = new \Exception();
        $this->quoteMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->model->add($giftMessages, $this->quoteMock);
    }

    public function testAddWithSaveMessageIdException()
    {
        $entityId = 12;
        $giftMessages = [
                'quote_item' => [
                    12 => [
                    'from' => 'sender',
                    'to' => 'recipient',
                    'message' => 'message',
                    ],
                ],
        ];
        $customerId = 42;

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($entityId)
            ->willReturn($this->quoteItemMock);
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->willReturn(null);
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->willReturn(33);
        $this->quoteItemMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->willReturn($this->quoteItemMock);
        $exception = new \Exception();
        $this->quoteItemMock->expects($this->once())->method('save')->will($this->throwException($exception));

        $this->model->add($giftMessages, $this->quoteMock);
    }

    public function testAddWithQuoteAddress()
    {
        $entityId = 1;
        $giftMessages = [
            'quote_address' => [
                1 => [
                    'from' => 'sender',
                    'to' => 'recipient',
                    'message' => 'message',
                ],
            ],
        ];
        $customerId = 42;

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())
            ->method('getAddressById')
            ->with($entityId)
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('getGiftMessageId')->willReturn(null);
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->willReturn(33);
        $this->quoteAddressMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('save');
        $this->model->add($giftMessages, $this->quoteMock);
    }

    public function testAddWithQuoteAddressItem()
    {
        $entityId = 1;
        $giftMessages = [
            'quote_address_item' => [
                1 => [
                    'from' => 'sender',
                    'to' => 'recipient',
                    'message' => 'message',
                    'address' => 'address',
                ],
            ],
        ];
        $customerId = 42;

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())
            ->method('getAddressById')
            ->with('address')
            ->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemById')
            ->with($entityId)
            ->willReturn($this->quoteAddressItemMock);
        $this->quoteAddressItemMock->expects($this->once())->method('getGiftMessageId')->willReturn(0);
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->willReturn($this->giftMessageMock);
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturn($this->giftMessageMock);
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->willReturn(33);
        $this->quoteAddressItemMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->willReturn($this->quoteAddressItemMock);
        $this->quoteAddressItemMock->expects($this->once())
            ->method('save')
            ->willReturn($this->quoteAddressItemMock);

        $this->model->add($giftMessages, $this->quoteMock);
    }

    /**
     */
    public function testSetMessageCouldNotAddGiftMessageException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The gift message couldn\'t be added to Cart.');

        $this->giftMessageMock->expects($this->once())->method('getSender')->willReturn('sender');
        $this->giftMessageMock->expects($this->once())->method('getRecipient')->willReturn('recipient');
        $this->giftMessageMock->expects($this->once())->method('getMessage')->willReturn('Message');

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception());

        $this->model->setMessage($this->quoteMock, 'item', $this->giftMessageMock);
    }
}

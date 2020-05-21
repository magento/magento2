<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as QuoteAddressItem;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GiftMessageManagerTest extends TestCase
{
    /**
     * @var GiftMessageManager
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $quoteItemMock;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject
     */
    protected $quoteAddressItemMock;

    /**
     * @var MockObject
     */
    protected $giftMessageMock;

    protected function setUp(): void
    {
        $this->messageFactoryMock =
            $this->getMockBuilder(MessageFactory::class)
                ->addMethods(['__wakeup'])
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setGiftMessageId', 'getGiftMessageId', 'getCustomerId'])
            ->onlyMethods([
                'save',
                'getItemById',
                'getAddressById',
                'getBillingAddress',
                'getShippingAddress',
                '__wakeup'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['setGiftMessageId', 'getGiftMessageId'])
            ->onlyMethods(['save', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getGiftMessageId', 'setGiftMessageId'])
            ->onlyMethods(['getItemById', 'save', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteAddressItemMock = $this->getMockBuilder(QuoteAddressItem::class)
            ->addMethods([
                'getGiftMessageId',
                'setGiftMessageId'
            ])
            ->onlyMethods(['save', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->giftMessageMock = $this->createPartialMock(
            Message::class,
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

        $this->model = new GiftMessageManager($this->messageFactoryMock);
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
        $this->quoteMock->expects($this->once())->method('save')->willThrowException($exception);

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
        $this->quoteItemMock->expects($this->once())->method('save')->willThrowException($exception);

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

    public function testSetMessageCouldNotAddGiftMessageException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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

<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\GiftMessageManager;

class GiftMessageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GiftMessageManager
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $giftMessageMock;

    protected function setUp()
    {
        $this->messageFactoryMock =
            $this->getMock('\Magento\GiftMessage\Model\MessageFactory', ['create', '__wakeup'], [], '', false);

        $this->quoteMock = $this->getMock('\Magento\Quote\Model\Quote',
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
            ],
            [],
            '',
            false);
        $this->quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item',
            [
                'setGiftMessageId',
                'getGiftMessageId',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false);

        $this->quoteAddressMock = $this->getMock('Magento\Quote\Model\Quote\Address',
            [
                'getGiftMessageId',
                'setGiftMessageId',
                'getItemById',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false);

        $this->quoteAddressItemMock = $this->getMock(
            '\Magento\Quote\Model\Quote\Address\Item',
            [
                'getGiftMessageId',
                'setGiftMessageId',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false);

        $this->giftMessageMock = $this->getMock('\Magento\GiftMessage\Model\Message',
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
                '__wakeup'
            ],
            [],
            '',
            false);

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
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue(null));
        $this->giftMessageMock->expects($this->never())->method('load');
        $this->giftMessageMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->giftMessageMock->expects($this->once())->method('delete');
        $this->quoteMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(0)
            ->will($this->returnValue($this->quoteMock));
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
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($entityId)
            ->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue(null));
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->will($this->returnValue(33));
        $this->quoteItemMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->will($this->returnValue($this->quoteItemMock));
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
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())
            ->method('getAddressById')
            ->with($entityId)
            ->will($this->returnValue($this->quoteAddressMock));
        $this->quoteAddressMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue(null));
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->will($this->returnValue(33));
        $this->quoteAddressMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->will($this->returnValue($this->quoteAddressMock));
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
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())
            ->method('getAddressById')
            ->with('address')
            ->will($this->returnValue($this->quoteAddressMock));
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemById')
            ->with($entityId)
            ->will($this->returnValue($this->quoteAddressItemMock));
        $this->quoteAddressItemMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue(0));
        $this->giftMessageMock->expects($this->once())
            ->method('setSender')
            ->with('sender')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setRecipient')
            ->with('recipient')
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())
            ->method('setMessage')
            ->with('message')
            ->will($this->returnValue($this->giftMessageMock));
        $this->quoteMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->giftMessageMock->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->will($this->returnValue($this->giftMessageMock));
        $this->giftMessageMock->expects($this->once())->method('save');
        $this->giftMessageMock->expects($this->once())->method('getId')->will($this->returnValue(33));
        $this->quoteAddressItemMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(33)
            ->will($this->returnValue($this->quoteAddressItemMock));
        $this->quoteAddressItemMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue($this->quoteAddressItemMock));

        $this->model->add($giftMessages, $this->quoteMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not add gift message to shopping cart
     */
    public function testSetMessageCouldNotAddGiftMessageException()
    {
        $this->giftMessageMock->expects($this->once())->method('getSender')->will($this->returnValue('sender'));
        $this->giftMessageMock->expects($this->once())->method('getRecipient')->will($this->returnValue('recipient'));
        $this->giftMessageMock->expects($this->once())->method('getMessage')->will($this->returnValue('Message'));

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception());

        $this->model->setMessage($this->quoteMock, 'item', $this->giftMessageMock);
    }
}

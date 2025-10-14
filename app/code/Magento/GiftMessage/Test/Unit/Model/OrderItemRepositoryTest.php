<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\OrderItemRepository;
use Magento\GiftMessage\Model\Save;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\GiftMessage\Model\OrderItemRepository
 * * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderItemRepositoryTest extends TestCase
{
    /**
     * @var OrderItemRepository|MockObject
     */
    private $orderItemRepository;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    private $orderMock;

    /**
     * @var Message|MockObject
     */
    private $helperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var MessageFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var Save|MockObject
     */
    private $giftMessageSaveModelMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getItemById', 'getIsVirtual'])
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageFactoryMock = $this->getMockBuilder(MessageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->giftMessageSaveModelMock = $this->getMockBuilder(Save::class)
            ->disableOriginalConstructor()
            ->addMethods(['setGiftmessages'])
            ->onlyMethods(['saveAllInOrder'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->orderItemRepository = $helper->getObject(
            OrderItemRepository::class,
            [
                'orderFactory' => $this->orderFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'helper' => $this->helperMock,
                'messageFactory' => $this->messageFactoryMock,
                'giftMessageSaveModel' => $this->giftMessageSaveModelMock
            ]
        );
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::get
     */
    public function testGet()
    {
        $orderId = 1;
        $orderItemId = 2;
        $messageId = 3;
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();
        $messageMock = $this->getMockBuilder(\Magento\GiftMessage\Model\Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(true);
        $orderItemMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn($messageId);
        $this->messageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($messageMock);
        $messageMock->expects($this->once())
            ->method('load')
            ->with($messageId)
            ->willReturnSelf();

        $this->assertEquals($messageMock, $this->orderItemRepository->get($orderId, $orderItemId));
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::get
     */
    public function testGetNoSuchEntityExceptionOnGetItemById()
    {
        $orderId = 1;
        $orderItemId = 2;

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn(null);
        $this->helperMock->expects($this->never())->method('isMessagesAllowed');

        try {
            $this->orderItemRepository->get($orderId, $orderItemId);
            $this->fail('Expected NoSuchEntityException not caught');
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals(
                'No item with the provided ID was found in the Order. Verify the ID and try again.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::get
     */
    public function testGetNoSuchEntityExceptionOnIsMessageAllowed()
    {
        $orderId = 1;
        $orderItemId = 2;
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(false);
        $orderItemMock->expects($this->never())->method('getGiftMessageId');

        try {
            $this->orderItemRepository->get($orderId, $orderItemId);
            $this->fail('Expected NoSuchEntityException not caught');
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals(
                "No item with the provided ID was found in the Order, or a gift message isn't allowed. "
                . "Verify and try again.",
                $exception->getMessage()
            );
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::get
     */
    public function testGetNoSuchEntityExceptionOnGetGiftMessageId()
    {
        $orderId = 1;
        $orderItemId = 2;
        $messageId = null;
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();

        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(true);
        $orderItemMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn($messageId);
        $this->messageFactoryMock->expects($this->never())->method('create');

        try {
            $this->orderItemRepository->get($orderId, $orderItemId);
            $this->fail('Expected NoSuchEntityException not caught');
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals(
                'No item with the provided ID was found in the Order. Verify the ID and try again.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::save
     */
    public function testSave()
    {
        $orderId = 1;
        $orderItemId = 2;
        $message[$orderItemId] = [
            'type' => 'order_item',
            'sender' => 'sender_value',
            'recipient' => 'recipient_value',
            'message' => 'message_value',
        ];
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->orderMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(true);
        $messageMock->expects($this->once())
            ->method('getSender')
            ->willReturn('sender_value');
        $messageMock->expects($this->once())
            ->method('getRecipient')
            ->willReturn('recipient_value');
        $messageMock->expects($this->once())
            ->method('getMessage')
            ->willReturn('message_value');
        $this->giftMessageSaveModelMock->expects($this->once())
            ->method('setGiftmessages')
            ->with($message);
        $this->giftMessageSaveModelMock->expects($this->once())
            ->method('saveAllInOrder');

        $this->assertTrue($this->orderItemRepository->save($orderId, $orderItemId, $messageMock));
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::save
     */
    public function testSaveNoSuchEntityException()
    {
        $orderId = 1;
        $orderItemId = 2;
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn(null);
        $this->orderMock->expects($this->never())
            ->method('getIsVirtual');

        try {
            $this->orderItemRepository->save($orderId, $orderItemId, $messageMock);
            $this->fail('Expected NoSuchEntityException not caught');
        } catch (NoSuchEntityException $exception) {
            $this->assertEquals(
                'No item with the provided ID was found in the Order. Verify the ID and try again.',
                $exception->getMessage()
            );
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::save
     */
    public function testSaveInvalidTransitionException()
    {
        $orderId = 1;
        $orderItemId = 2;
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->orderMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(true);
        $this->helperMock->expects($this->never())
            ->method('isMessagesAllowed');

        try {
            $this->orderItemRepository->save($orderId, $orderItemId, $messageMock);
            $this->fail('Expected InvalidTransitionException not caught');
        } catch (InvalidTransitionException $exception) {
            $this->assertEquals("Gift messages can't be used for virtual products.", $exception->getMessage());
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::save
     */
    public function testSaveCouldNotSaveException()
    {
        $orderId = 1;
        $orderItemId = 2;
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->orderMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(false);
        $messageMock->expects($this->never())
            ->method('getSender');

        try {
            $this->orderItemRepository->save($orderId, $orderItemId, $messageMock);
            $this->fail('Expected CouldNotSaveException not caught');
        } catch (CouldNotSaveException $exception) {
            $this->assertEquals("The gift message isn't available.", $exception->getMessage());
        }
    }

    /**
     * @covers \Magento\GiftMessage\Model\OrderItemRepository::save
     */
    public function testSaveCouldNotSaveExceptionOnSaveAllInOrder()
    {
        $orderId = 1;
        $orderItemId = 2;
        $message[$orderItemId] = [
            'type' => 'order_item',
            'sender' => 'sender_value',
            'recipient' => 'recipient_value',
            'message' => 'message_value',
        ];
        $excep = new \Exception('Exception message');
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMock();
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);
        $this->orderMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('order_item', $orderItemMock, $this->storeMock)
            ->willReturn(true);
        $messageMock->expects($this->once())
            ->method('getSender')
            ->willReturn('sender_value');
        $messageMock->expects($this->once())
            ->method('getRecipient')
            ->willReturn('recipient_value');
        $messageMock->expects($this->once())
            ->method('getMessage')
            ->willReturn('message_value');
        $this->giftMessageSaveModelMock->expects($this->once())
            ->method('setGiftmessages')
            ->with($message);
        $this->giftMessageSaveModelMock->expects($this->once())
            ->method('saveAllInOrder')
            ->willThrowException($excep);

        try {
            $this->orderItemRepository->save($orderId, $orderItemId, $messageMock);
            $this->fail('Expected CouldNotSaveException not caught');
        } catch (CouldNotSaveException $exception) {
            $this->assertEquals(
                'The gift message couldn\'t be added to the "' . $excep->getMessage() . '" order.',
                $exception->getMessage()
            );
        }
    }
}

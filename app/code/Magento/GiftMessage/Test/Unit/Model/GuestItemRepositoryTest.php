<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\ItemRepository;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestItemRepositoryTest extends TestCase
{
    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * @var MockObject
     */
    protected $quoteRepositoryMock;

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
    protected $messageMock;

    /**
     * @var MockObject
     */
    protected $quoteItemMock;

    /**
     * @var string
     */
    protected $cartId = 13;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $giftMessageManagerMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->messageFactoryMock = $this->getMockBuilder(MessageFactory::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageMock = $this->createMock(Message::class);
        $this->quoteItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getGiftMessageId'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getGiftMessageId'])
            ->onlyMethods(['getItemById', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->giftMessageManagerMock =
            $this->createMock(GiftMessageManager::class);
        $this->helperMock = $this->createMock(\Magento\GiftMessage\Helper\Message::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->itemRepository = new ItemRepository(
            $this->quoteRepositoryMock,
            $this->storeManagerMock,
            $this->giftMessageManagerMock,
            $this->helperMock,
            $this->messageFactoryMock
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($this->cartId)
            ->willReturn($this->quoteMock);
    }

    public function testGetWithNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'No item with the provided ID was found in the Cart. Verify the ID and try again.'
        );
        $itemId = 2;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->willReturn(null);

        $this->itemRepository->get($this->cartId, $itemId);
    }

    public function testGetWithoutMessageId()
    {
        $messageId = 0;
        $itemId = 2;

        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($this->quoteItemMock);
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);

        $this->assertNull($this->itemRepository->get($this->cartId, $itemId));
    }

    public function testGet()
    {
        $messageId = 123;
        $itemId = 2;

        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($this->quoteItemMock);
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->messageMock);
        $this->messageMock->expects($this->once())
            ->method('load')
            ->with($messageId)
            ->willReturn($this->messageMock);

        $this->assertEquals($this->messageMock, $this->itemRepository->get($this->cartId, $itemId));
    }

    public function testSaveWithNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $itemId = 1;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->willReturn(null);
        $this->itemRepository->save($this->cartId, $this->messageMock, $itemId);

        $this->expectExceptionMessage(
            'No product with the "1" itemId exists in the Cart. Verify your information and try again.'
        );
    }

    public function testSaveWithInvalidTransitionException()
    {
        $this->expectException('Magento\Framework\Exception\State\InvalidTransitionException');
        $this->expectExceptionMessage('Gift messages can\'t be used for virtual products.');
        $itemId = 1;

        $quoteItem = $this->getMockBuilder(Item::class)
            ->addMethods(['getIsVirtual'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($quoteItem);
        $quoteItem->expects($this->once())->method('getIsVirtual')->willReturn(1);

        $this->itemRepository->save($this->cartId, $this->messageMock, $itemId);
    }

    public function testSave()
    {
        $itemId = 1;

        $quoteItem = $this->getMockBuilder(Item::class)
            ->addMethods(['getIsVirtual'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($quoteItem);
        $quoteItem->expects($this->once())->method('getIsVirtual')->willReturn(0);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('items', $this->quoteMock, $this->storeMock)
            ->willReturn(true);
        $this->giftMessageManagerMock->expects($this->once())
            ->method('setMessage')
            ->with($this->quoteMock, 'quote_item', $this->messageMock, $itemId)
            ->willReturn($this->giftMessageManagerMock);
        $this->messageMock->expects($this->once())->method('getMessage')->willReturn('message');

        $this->assertTrue($this->itemRepository->save($this->cartId, $this->messageMock, $itemId));
    }
}

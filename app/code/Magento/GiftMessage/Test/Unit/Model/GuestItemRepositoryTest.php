<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->messageFactoryMock = $this->createPartialMock(
            MessageFactory::class,
            [
                'create',
                '__wakeup'
            ]
        );
        $this->messageMock = $this->createMock(Message::class);
        $this->quoteItemMock = $this->createPartialMock(
            Item::class,
            [
                'getGiftMessageId',
                '__wakeup'
            ]
        );
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            [
                'getGiftMessageId',
                'getItemById',
                '__wakeup',
            ]
        );
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
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
            ->will($this->returnValue($this->quoteMock));
    }

    public function testGetWithNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'No item with the provided ID was found in the Cart. Verify the ID and try again.'
        );
        $itemId = 2;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));

        $this->itemRepository->get($this->cartId, $itemId);
    }

    public function testGetWithoutMessageId()
    {
        $messageId = 0;
        $itemId = 2;

        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($this->quoteItemMock));
        $this->quoteItemMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));

        $this->assertNull($this->itemRepository->get($this->cartId, $itemId));
    }

    public function testGet()
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

        $this->assertEquals($this->messageMock, $this->itemRepository->get($this->cartId, $itemId));
    }

    public function testSaveWithNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $itemId = 1;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));
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

        $quoteItem = $this->createPartialMock(Item::class, ['getIsVirtual', '__wakeup']);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($quoteItem));
        $quoteItem->expects($this->once())->method('getIsVirtual')->will($this->returnValue(1));

        $this->itemRepository->save($this->cartId, $this->messageMock, $itemId);
    }

    public function testSave()
    {
        $itemId = 1;

        $quoteItem = $this->createPartialMock(Item::class, ['getIsVirtual', '__wakeup']);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->will($this->returnValue($quoteItem));
        $quoteItem->expects($this->once())->method('getIsVirtual')->will($this->returnValue(0));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('items', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(true));
        $this->giftMessageManagerMock->expects($this->once())
            ->method('setMessage')
            ->with($this->quoteMock, 'quote_item', $this->messageMock, $itemId)
            ->will($this->returnValue($this->giftMessageManagerMock));
        $this->messageMock->expects($this->once())->method('getMessage')->willReturn('message');

        $this->assertTrue($this->itemRepository->save($this->cartId, $this->messageMock, $itemId));
    }
}

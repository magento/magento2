<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\ItemRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ItemRepository
     */
    protected $itemRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteRepositoryMock;

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
    protected $messageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemMock;

    /**
     * @var string
     */
    protected $cartId = 13;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $giftMessageManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->messageFactoryMock = $this->createPartialMock(
            \Magento\GiftMessage\Model\MessageFactory::class,
            [
                'create',
                '__wakeup'
            ]
        );
        $this->messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);
        $this->quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            [
                'getGiftMessageId',
                '__wakeup'
            ]
        );
        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getGiftMessageId',
                'getItemById',
                '__wakeup',
            ]
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->giftMessageManagerMock =
            $this->createMock(\Magento\GiftMessage\Model\GiftMessageManager::class);
        $this->helperMock = $this->createMock(\Magento\GiftMessage\Helper\Message::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->itemRepository = new \Magento\GiftMessage\Model\ItemRepository(
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

    /**
     */
    public function testGetWithNoSuchEntityException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No item with the provided ID was found in the Cart. Verify the ID and try again.');

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

    /**
     */
    public function testSaveWithNoSuchEntityException()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $itemId = 1;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));
        $this->itemRepository->save($this->cartId, $this->messageMock, $itemId);

        $this->expectExceptionMessage(
            'No product with the "1" itemId exists in the Cart. Verify your information and try again.'
        );
    }

    /**
     */
    public function testSaveWithInvalidTransitionException()
    {
        $this->expectException(\Magento\Framework\Exception\State\InvalidTransitionException::class);
        $this->expectExceptionMessage('Gift messages can\'t be used for virtual products.');

        $itemId = 1;

        $quoteItem = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getIsVirtual', '__wakeup']);
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

        $quoteItem = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getIsVirtual', '__wakeup']);
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

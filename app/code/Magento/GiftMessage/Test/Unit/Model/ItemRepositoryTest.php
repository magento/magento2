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
    protected $cartId = 13;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $giftMessageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no item with provided id in the cart
     */
    public function testGetWithNoSuchEntityException()
    {
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no product with provided  itemId: 1 in the cart
     */
    public function testSaveWithNoSuchEntityException()
    {
        $itemId = 1;

        $this->quoteMock->expects($this->once())->method('getItemById')->with($itemId)->will($this->returnValue(null));

        $this->itemRepository->save($this->cartId, $this->messageMock, $itemId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages are not applicable for virtual products
     */
    public function testSaveWithInvalidTransitionException()
    {
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

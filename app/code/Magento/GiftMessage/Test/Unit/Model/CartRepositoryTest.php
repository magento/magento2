<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Model\CartRepository;
use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartRepositoryTest extends TestCase
{
    /**
     * @var CartRepository
     */
    protected $cartRepository;

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
            ->onlyMethods(['getItemById', 'getItemsCount', 'isVirtual', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->giftMessageManagerMock =
            $this->createMock(GiftMessageManager::class);
        $this->helperMock = $this->createMock(\Magento\GiftMessage\Helper\Message::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->cartRepository = new CartRepository(
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

    public function testGetWithOutMessageId()
    {
        $messageId = 0;
        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
        $this->assertNull($this->cartRepository->get($this->cartId));
    }

    public function testGet()
    {
        $messageId = 156;

        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->messageMock);
        $this->messageMock->expects($this->once())->method('load')->willReturn($this->messageMock);

        $this->assertEquals($this->messageMock, $this->cartRepository->get($this->cartId));
    }

    public function testSaveWithInputException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Gift messages can\'t be used for an empty cart. Add an item and try again.');
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->cartRepository->save($this->cartId, $this->messageMock);
    }

    public function testSaveWithInvalidTransitionException()
    {
        $this->expectException('Magento\Framework\Exception\State\InvalidTransitionException');
        $this->expectExceptionMessage('Gift messages can\'t be used for virtual products.');
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(true);
        $this->cartRepository->save($this->cartId, $this->messageMock);
    }

    public function testSave()
    {
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('quote', $this->quoteMock, $this->storeMock)
            ->willReturn(true);
        $this->giftMessageManagerMock->expects($this->once())
            ->method('setMessage')
            ->with($this->quoteMock, 'quote', $this->messageMock)
            ->willReturn($this->giftMessageManagerMock);
        $this->messageMock->expects($this->once())->method('getMessage')->willReturn('message');

        $this->assertTrue($this->cartRepository->save($this->cartId, $this->messageMock));
    }
}

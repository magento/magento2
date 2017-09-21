<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

// @codingStandardsIgnoreFile

use Magento\GiftMessage\Model\CartRepository;

class CartRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartRepository
     */
    protected $cartRepository;

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
        $this->messageFactoryMock = $this->createPartialMock(\Magento\GiftMessage\Model\MessageFactory::class, [
                'create',
                '__wakeup'
            ]);
        $this->messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);
        $this->quoteItemMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, [
                'getGiftMessageId',
                '__wakeup'
            ]);
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'getGiftMessageId',
                'getItemById',
                'getItemsCount',
                'isVirtual',
                '__wakeup',
            ]);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->giftMessageManagerMock =
            $this->createMock(\Magento\GiftMessage\Model\GiftMessageManager::class);
        $this->helperMock = $this->createMock(\Magento\GiftMessage\Helper\Message::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->cartRepository = new \Magento\GiftMessage\Model\CartRepository(
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

    public function testGetWithOutMessageId()
    {
        $messageId = 0;

        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));

        $this->assertNull($this->cartRepository->get($this->cartId));
    }

    public function testGet()
    {
        $messageId = 156;

        $this->quoteMock->expects($this->once())->method('getGiftMessageId')->will($this->returnValue($messageId));
        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->messageMock));
        $this->messageMock->expects($this->once())->method('load')->will($this->returnValue($this->messageMock));

        $this->assertEquals($this->messageMock, $this->cartRepository->get($this->cartId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Gift Messages are not applicable for empty cart
     */
    public function testSaveWithInputException()
    {
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(0));

        $this->cartRepository->save($this->cartId, $this->messageMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages are not applicable for virtual products
     */
    public function testSaveWithInvalidTransitionException()
    {
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(true));
        $this->cartRepository->save($this->cartId, $this->messageMock);
    }

    public function testSave()
    {
        $this->quoteMock->expects($this->once())->method('isVirtual')->will($this->returnValue(false));
        $this->quoteMock->expects($this->once())->method('getItemsCount')->will($this->returnValue(1));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->helperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->with('quote', $this->quoteMock, $this->storeMock)
            ->will($this->returnValue(true));
        $this->giftMessageManagerMock->expects($this->once())
            ->method('setMessage')
            ->with($this->quoteMock, 'quote', $this->messageMock)
            ->will($this->returnValue($this->giftMessageManagerMock));
        $this->messageMock->expects($this->once())->method('getMessage')->willReturn('message');

        $this->assertTrue($this->cartRepository->save($this->cartId, $this->messageMock));
    }
}

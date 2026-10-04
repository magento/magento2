<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\GuestCart\GuestCartItemRepository;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartItemRepositoryTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var GuestCartItemRepository
     */
    protected $guestCartItemRepository;

    /**
     * @var MockObject
     */
    protected $cartItemRepositoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var MockObject
     */
    protected $quoteItemMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var string
     */
    protected $cartId;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 33;

        // Create QuoteIdMask mock
        $this->quoteIdMaskMock = $this->createPartialMockWithReflection(
            QuoteIdMask::class,
            ["load", "getQuoteId", "getMaskedId"]
        );
        $this->quoteIdMaskMock->method('load')->with($this->maskedCartId)->willReturnSelf();
        $this->quoteIdMaskMock->method('getQuoteId')->willReturn($this->cartId);
        $this->quoteIdMaskMock->method('getMaskedId')->willReturn($this->maskedCartId);
        
        // Create QuoteIdMaskFactory mock
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskFactoryMock->method('create')->willReturn($this->quoteIdMaskMock);

        $this->quoteItemMock = $this->createMock(Item::class);
        $this->quoteItemMock->method('getItemId')->willReturn($this->maskedCartId);
        $this->quoteItemMock->method('getQuoteId')->willReturn($this->maskedCartId);
        $this->quoteItemMock->expects($this->any())
            ->method('setQuoteId')
            ->with($this->cartId);

        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->guestCartItemRepository =
            $objectManager->getObject(
                GuestCartItemRepository::class,
                [
                    'repository' => $this->cartItemRepositoryMock,
                    'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                ]
            );
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $expectedValue = 'expected value';
        $this->cartItemRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturn($expectedValue);
        $this->assertEquals($expectedValue, $this->guestCartItemRepository->save($this->quoteItemMock));
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->any())
            ->method('setQuoteId')
            ->with($this->maskedCartId);
        $this->cartItemRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->cartId)
            ->willReturn([$itemMock]);
        $this->assertEquals([$itemMock], $this->guestCartItemRepository->getList($this->maskedCartId));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $itemId = 5;
        $this->cartItemRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($this->cartId, $itemId)
            ->willReturn(true);
        $this->assertTrue($this->guestCartItemRepository->deleteById($this->maskedCartId, $itemId));
    }
}

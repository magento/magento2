<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestCartItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestCartItemRepository
     */
    protected $guestCartItemRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartItemRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 33;

        /**
         * @var \Magento\Quote\Test\Unit\Model\GuestCart\GuestCartTestHelper
         */
        $guestCartTestHelper = new \Magento\Quote\Test\Unit\Model\GuestCart\GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) =
            $guestCartTestHelper->mockQuoteIdMask(
                $this->maskedCartId,
                $this->cartId
            );

        $this->quoteIdMaskMock->expects($this->any())
            ->method('getMaskedId')
            ->willReturn($this->maskedCartId);

        $this->quoteItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->quoteItemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($this->maskedCartId);
        $this->quoteItemMock->expects($this->any())
            ->method('getQuoteId')
            ->willReturn($this->maskedCartId);
        $this->quoteItemMock->expects($this->any())
            ->method('setQuoteId')
            ->with($this->cartId);

        $this->cartItemRepositoryMock = $this->createMock(\Magento\Quote\Api\CartItemRepositoryInterface::class);
        $this->guestCartItemRepository =
            $objectManager->getObject(
                \Magento\Quote\Model\GuestCart\GuestCartItemRepository::class,
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
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
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

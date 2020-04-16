<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestCartManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagementMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteRepositoryMock;

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
    protected $cartRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Quote\Model\GuestCart\GuestCartManagement
     */
    protected $guestCartManagement;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteManagementMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create']
        );
        $this->quoteIdMaskMock = $this->createPartialMock(
            \Magento\Quote\Model\QuoteIdMask::class,
            ['getQuoteId', 'getMaskedId', 'load', 'save', 'setQuoteId']
        );

        $this->cartRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $this->quoteMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCheckoutMethod']
        );

        $this->guestCartManagement = $objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestCartManagement::class,
            [
                'quoteManagement' => $this->quoteManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'cartRepository' => $this->cartRepositoryMock
            ]
        );
    }

    public function testCreateEmptyCart()
    {
        $maskedCartId = 'masked1cart2id3';
        $cartId = 1;
        $this->quoteIdMaskMock->expects($this->once())->method('setQuoteId')->with($cartId)->willReturnSelf();
        $this->quoteIdMaskMock->expects($this->once())->method('save')->willReturnSelf();
        $this->quoteIdMaskMock->expects($this->once())->method('getMaskedId')->willReturn($maskedCartId);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteManagementMock->expects($this->once())->method('createEmptyCart')->willReturn($cartId);

        $this->assertEquals($maskedCartId, $this->guestCartManagement->createEmptyCart());
    }

    public function testAssignCustomer()
    {
        $maskedCartId = 'masked1cart2id3';
        $cartId = 1;
        $customerId = 1;
        $storeId = 1;

        $this->quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $this->quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($maskedCartId);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteManagementMock->expects($this->once())->method('assignCustomer')->willReturn(true);

        $this->assertTrue($this->guestCartManagement->assignCustomer($cartId, $customerId, $storeId));
    }

    public function testPlaceOrder()
    {
        $maskedCartId = 'masked1cart2id3';
        $cartId = 1;
        $orderId = 1;

        $this->quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $this->cartRepositoryMock->expects($this->once())->method('get')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('setCheckoutMethod');
        $this->quoteIdMaskMock->expects($this->any())->method('getQuoteId')->willReturn($maskedCartId);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteManagementMock->expects($this->once())->method('placeOrder')->willReturn($orderId);

        $this->assertEquals($orderId, $this->guestCartManagement->placeOrder($cartId));
    }
}

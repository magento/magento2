<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\GuestCart\GuestCartManagement;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartManagementTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $quoteManagementMock;

    /**
     * @var MockObject
     */
    protected $quoteRepositoryMock;

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
    protected $cartRepositoryMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var GuestCartManagement
     */
    protected $guestCartManagement;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->quoteManagementMock = $this->getMockForAbstractClass(
            CartManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->quoteIdMaskFactoryMock = $this->createPartialMock(
            QuoteIdMaskFactory::class,
            ['create']
        );
        $this->quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId', 'getMaskedId', 'setQuoteId'])
            ->onlyMethods(['load', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->quoteMock = $this->getMockForAbstractClass(
            CartInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCheckoutMethod']
        );

        $this->guestCartManagement = $objectManager->getObject(
            GuestCartManagement::class,
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

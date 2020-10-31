<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Model\Cart\CartTotalRepositoryPlugin;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CartTotalRepository;
use Magento\Quote\Model\Cart\Totals as QuoteTotals;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Rate as QuoteAddressRate;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartTotalRepositoryPluginTest extends TestCase
{
    /**
     * Stub cart id
     */
    private const STUB_CART_ID = 10;

    /**
     * Stub shipping method
     */
    private const STUB_SHIPPING_METHOD = 'flatrate_flatrate';

    /**
     * Stub shipping price
     */
    private const STUB_SHIPPING_PRICE = '10.00';

    /**
     * @var CartTotalRepositoryPlugin
     */
    private $modelRepository;

    /**
     * @var CartTotalRepository|MockObject
     */
    private $quoteTotalRepositoryMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var QuoteTotals|MockObject
     */
    private $quoteTotalsMock;

    /**
     * @var QuoteAddress|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var QuoteAddressRate|MockObject
     */
    private $shippingRateMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->quoteTotalsMock = $this->getMockBuilder(QuoteTotals::class)
            ->addMethods(['getStore', 'getShippingAddress', 'getIsMultiShipping'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAddressMock = $this->getMockBuilder(QuoteAddress::class)
            ->addMethods(['getShippingAmount'])
            ->onlyMethods(['getShippingMethod', 'getShippingRateByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingRateMock = $this->getMockBuilder(QuoteAddressRate::class)
            ->addMethods(['getPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->quoteTotalRepositoryMock = $this->createMock(CartTotalRepository::class);
        $this->modelRepository = $objectManager->getObject(CartTotalRepositoryPlugin::class, [
            'quoteRepository' => $this->quoteRepositoryMock
        ]);
    }

    /**
     * Test quoteTotal from cartRepository after get($cartId) function is called
     */
    public function testAfterGetQuoteTotalAddedShippingPrice()
    {
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with(self::STUB_CART_ID)
            ->willReturn($this->quoteTotalsMock);
        $this->quoteTotalsMock->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(true);
        $this->quoteTotalsMock->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingMethod')
            ->willReturn(self::STUB_SHIPPING_METHOD);
        $this->shippingAddressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(self::STUB_SHIPPING_PRICE);

        $this->shippingAddressMock->expects($this->once())
            ->method('getShippingRateByCode')
            ->willReturn($this->shippingRateMock);

        $this->shippingRateMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(self::STUB_SHIPPING_PRICE);

        $this->quoteTotalsMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturnSelf();

        $this->modelRepository->afterGet(
            $this->quoteTotalRepositoryMock,
            $this->quoteTotalsMock,
            self::STUB_CART_ID
        );
    }
}

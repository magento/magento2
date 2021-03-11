<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Model\Cart;

class CartTotalRepositoryPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Multishipping\Model\Cart\CartTotalRepositoryPlugin
     */
    private $modelRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->modelRepository = new \Magento\Multishipping\Model\Cart\CartTotalRepositoryPlugin(
            $this->quoteRepositoryMock
        );
    }

    /**
     * Test quotTotal from cartRepository after get($cartId) function is called
     */
    public function testAfterGet()
    {
        $cartId = "10";
        $shippingMethod = 'flatrate_flatrate';
        $shippingPrice = '10.00';
        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Cart\Totals::class,
            [
            'getStore',
            'getShippingAddress',
            'getIsMultiShipping'
            ]
        );
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getIsMultiShipping')->willReturn(true);
        $shippingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
            'getShippingMethod',
            'getShippingRateByCode',
            'getShippingAmount'
            ]
        );
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddressMock);

        $shippingAddressMock->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $shippingAddressMock->expects($this->any())->method('getShippingAmount')->willReturn($shippingPrice);
        $shippingRateMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Rate::class,
            [
            'getPrice'
            ]
        );
        $shippingAddressMock->expects($this->once())->method('getShippingRateByCode')->willReturn($shippingRateMock);

        $shippingRateMock->expects($this->once())->method('getPrice')->willReturn($shippingPrice);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->any())->method('getBaseCurrency')->willReturnSelf();

        $this->modelRepository->afterGet(
            $this->createMock(\Magento\Quote\Model\Cart\CartTotalRepository::class),
            $quoteMock,
            $cartId
        );
    }
}

<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Checkout\Model\TotalsInformationManagement as TotalsInformationManagementModel;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Test\Unit\Helper\AddressShippingInfoTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;

class TotalsInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var CartTotalRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartTotalRepositoryMock;

    /**
     * @var TotalsInformationManagement
     */
    private $totalsInformationManagement;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->createMock(
            CartRepositoryInterface::class
        );
        $this->cartTotalRepositoryMock = $this->createMock(
            CartTotalRepositoryInterface::class
        );
        $this->totalsInformationManagement = new TotalsInformationManagementModel(
            $this->cartRepositoryMock,
            $this->cartTotalRepositoryMock
        );
    }

    /**
     * Test for \Magento\Checkout\Model\TotalsInformationManagement::calculate.
     *
     * @param string|null $carrierCode
     * @param string|null $carrierMethod
     * @param int $methodSetCount
     */
    #[DataProvider('dataProviderCalculate')]
    public function testCalculate(?string $carrierCode, ?string $carrierMethod, int $methodSetCount)
    {
        $cartId = 1;
        $cartMock = $this->createMock(
            Quote::class
        );
        $cartMock->expects($this->once())->method('getItemsCount')->willReturn(1);
        $cartMock->expects($this->once())->method('getIsVirtual')->willReturn(false);
        $this->cartRepositoryMock->expects($this->once())->method('get')->with($cartId)->willReturn($cartMock);
        $this->cartTotalRepositoryMock->expects($this->once())->method('get')->with($cartId);

        $addressInformationMock = $this->createMock(
            TotalsInformationInterface::class
        );
        $addressMock = new AddressShippingInfoTestHelper();

        $addressInformationMock->expects($this->once())->method('getAddress')->willReturn($addressMock);
        $addressInformationMock->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->method('getShippingMethodCode')->willReturn($carrierMethod);
        $cartMock->expects($this->once())->method('setShippingAddress')->with($addressMock);
        $cartMock->expects($this->exactly($methodSetCount))->method('getShippingAddress')->willReturn($addressMock);
        $cartMock->expects($this->once())->method('collectTotals');

        $this->totalsInformationManagement->calculate($cartId, $addressInformationMock);

        if ($methodSetCount > 0) {
            $this->assertTrue($addressMock->getCollectShippingRatesFlag());
            $this->assertSame($carrierCode . '_' . $carrierMethod, $addressMock->getShippingMethod());
        }
    }

    /**
     * Test case when shipping amount must be reset to 0 because of changed shipping method.
     */
    public function testResetShippingAmount()
    {
        $cartId = 1;
        $carrierCode = 'carrier_code';
        $carrierMethod = 'carrier_method';

        $cartMock = $this->createMock(Quote::class);
        $cartMock->method('getItemsCount')
            ->willReturn(1);
        $cartMock->method('getIsVirtual')
            ->willReturn(false);
        $this->cartRepositoryMock->method('get')->with($cartId)->willReturn($cartMock);
        $this->cartTotalRepositoryMock->method('get')->with($cartId);

        $addressInformationMock = $this->createMock(TotalsInformationInterface::class);
        $addressMock = new AddressShippingInfoTestHelper();
        $addressMock->setShippingMethod('flatrate_flatrate');

        $addressInformationMock->method('getAddress')
            ->willReturn($addressMock);
        $addressInformationMock->method('getShippingCarrierCode')
            ->willReturn($carrierCode);
        $addressInformationMock->method('getShippingMethodCode')
            ->willReturn($carrierMethod);
        $cartMock->method('setShippingAddress')
            ->with($addressMock);
        $cartMock->method('getShippingAddress')
            ->willReturn($addressMock);
        $cartMock->expects($this->once())
            ->method('collectTotals');

        $this->totalsInformationManagement->calculate($cartId, $addressInformationMock);

        $this->assertTrue($addressMock->getCollectShippingRatesFlag());
        $this->assertSame(0, $addressMock->getShippingAmount());
        $this->assertSame(0, $addressMock->getBaseShippingAmount());
        $this->assertSame($carrierCode . '_' . $carrierMethod, $addressMock->getShippingMethod());
    }

    /**
     * Data provider for testCalculate.
     *
     * @return array
     */
    public static function dataProviderCalculate(): array
    {
        return [
            [
                null,
                null,
                0
            ],
            [
                null,
                'carrier_method',
                0
            ],
            [
                'carrier_code',
                null,
                0
            ],
            [
                'carrier_code',
                'carrier_method',
                1
            ]
        ];
    }
}

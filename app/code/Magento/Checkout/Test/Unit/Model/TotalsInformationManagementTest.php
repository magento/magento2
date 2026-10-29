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
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class TotalsInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    use MockCreationTrait;
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

        $addressInformationMock = $this->createMock(TotalsInformationInterface::class);
        $addressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['setCollectShippingRates', 'setShippingMethod', 'getShippingMethod', 'getCollectShippingRatesFlag', 'save']
        );
        $addressMock->method('save')->willReturnSelf();

        if ($methodSetCount > 0) {
            $expectedMethod = $carrierCode . '_' . $carrierMethod;
            $addressMock->method('getShippingMethod')->willReturnOnConsecutiveCalls(null, $expectedMethod);
            $addressMock->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
            $addressMock->expects($this->once())->method('setShippingMethod')->with($expectedMethod)->willReturnSelf();
            $addressMock->method('getCollectShippingRatesFlag')->willReturn(true);
        } else {
            $addressMock->method('getShippingMethod')->willReturn(null);
        }

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
        $addressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['setCollectShippingRates', 'setShippingMethod', 'getShippingMethod',
             'setShippingAmount', 'setBaseShippingAmount', 'getCollectShippingRatesFlag',
             'getShippingAmount', 'getBaseShippingAmount', 'save']
        );
        $expectedMethod = $carrierCode . '_' . $carrierMethod;
        // getShippingMethod called twice in condition (line 240-241), once in assertion
        $addressMock->method('getShippingMethod')->willReturnOnConsecutiveCalls(
            'flatrate_flatrate',
            'flatrate_flatrate',
            $expectedMethod
        );
        $addressMock->expects($this->once())->method('setShippingAmount')->with(0)->willReturnSelf();
        $addressMock->expects($this->once())->method('setBaseShippingAmount')->with(0)->willReturnSelf();
        $addressMock->expects($this->once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
        $addressMock->expects($this->once())->method('setShippingMethod')->with($expectedMethod)->willReturnSelf();
        $addressMock->method('getCollectShippingRatesFlag')->willReturn(true);
        $addressMock->method('getShippingAmount')->willReturn(0);
        $addressMock->method('getBaseShippingAmount')->willReturn(0);
        $addressMock->method('save')->willReturnSelf();

        $addressInformationMock->method('getAddress')->willReturn($addressMock);
        $addressInformationMock->method('getShippingCarrierCode')->willReturn($carrierCode);
        $addressInformationMock->method('getShippingMethodCode')->willReturn($carrierMethod);
        $cartMock->method('setShippingAddress')->with($addressMock);
        $cartMock->method('getShippingAddress')->willReturn($addressMock);
        $cartMock->expects($this->once())->method('collectTotals');

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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Calculation;

class TotalBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $totalBaseCalculator;

    public function testCalculateWithTaxInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(3))
            ->method('deltaRound')->willReturn(0);
        $this->initMocks(true);

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->totalBaseCalculator)
        );
        $this->assertEquals(self::UNIT_PRICE_INCL_TAX_ROUNDED, $this->taxDetailsItem->getPriceInclTax());
    }

    public function testCalculateWithTaxInPriceNoRounding()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(3))
            ->method('deltaRound')->willReturn(0);
        $this->initMocks(true);

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->totalBaseCalculator, false)
        );
        $this->assertEquals(self::UNIT_PRICE_INCL_TAX, $this->taxDetailsItem->getPriceInclTax());
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(2))
            ->method('deltaRound')->willReturn(0);
        $this->initMocks(false);

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->totalBaseCalculator)
        );
    }

    private function initTotalBaseCalculator()
    {
        $taxClassService = $this->createMock(\Magento\Tax\Api\TaxClassManagementInterface::class);
        $this->totalBaseCalculator = $this->getMockBuilder(\Magento\Tax\Model\Calculation\TotalBaseCalculator::class)
            ->setMethods(['deltaRound'])
            ->setConstructorArgs(
                [
                    'taxClassService' => $taxClassService,
                    'taxDetailsItemDataObjectFactory' => $this->taxItemDetailsDataObjectFactory,
                    'appliedTaxDataObjectFactory' => $this->appliedTaxDataObjectFactory,
                    'appliedTaxRateDataObjectFactory' => $this->appliedTaxRateDataObjectFactory,
                    'calculationTool' => $this->mockCalculationTool,
                    'config' => $this->mockConfig,
                    'storeId' => self::STORE_ID,
                    'addressRateRequest' => $this->addressRateRequest
                ]
            )
            ->getMock();
    }
}

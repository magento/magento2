<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

class TotalBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $totalBaseCalculator;

    public function testCalculateWithTaxInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(3))
            ->method('deltaRound')->will($this->returnValue(0));
        $this->initMocks(true);

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->totalBaseCalculator)
        );
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $this->initTotalBaseCalculator();
        $this->totalBaseCalculator->expects($this->exactly(2))
            ->method('deltaRound')->will($this->returnValue(0));
        $this->initMocks(false);

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->totalBaseCalculator)
        );
    }

    private function initTotalBaseCalculator()
    {
        $taxClassService = $this->getMock('Magento\Tax\Api\TaxClassManagementInterface');
        $this->totalBaseCalculator = $this->getMock(
            'Magento\Tax\Model\Calculation\TotalBaseCalculator',
            ['deltaRound'],
            [
                'taxClassService' => $taxClassService,
                'taxDetailsItemBuilder' => $this->taxItemDetailsBuilder,
                'appliedTaxBuilder' => $this->appliedTaxBuilder,
                'appliedRateBuilder' => $this->appliedTaxRateBuilder,
                'calculationTool' => $this->mockCalculationTool,
                'config' => $this->mockConfig,
                'storeId' => self::STORE_ID,
                'addressRateRequest' => $this->addressRateRequest
            ]
        );
    }
}

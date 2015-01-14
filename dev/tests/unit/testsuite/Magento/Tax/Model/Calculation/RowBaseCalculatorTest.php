<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

/**
 * Class RowBaseCalculatorTest
 *
 */
class RowBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /** @var RowBaseCalculator | \PHPUnit_Framework_MockObject_MockObject */
    protected $rowBaseCalculator;

    public function testCalculateWithTaxInPrice()
    {
        $this->initMocks(true);
        $this->initRowBaseCalculator();
        $this->rowBaseCalculator->expects($this->once())
            ->method('deltaRound')->will($this->returnValue(0));

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->rowBaseCalculator)
        );
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $this->initMocks(false);
        $this->initRowBaseCalculator();
        $this->rowBaseCalculator->expects($this->never())
            ->method('deltaRound');

        $this->assertSame(
            self::EXPECTED_VALUE,
            $this->calculate($this->rowBaseCalculator)
        );
    }

    private function initRowBaseCalculator()
    {
        $taxClassService = $this->getMock('Magento\Tax\Api\TaxClassManagementInterface');
        $this->rowBaseCalculator = $this->getMock(
            'Magento\Tax\Model\Calculation\RowBaseCalculator',
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

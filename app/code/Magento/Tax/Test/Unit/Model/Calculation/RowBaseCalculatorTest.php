<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation\RowBaseCalculator;
use PHPUnit\Framework\MockObject\MockObject;

class RowBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /** @var RowBaseCalculator|MockObject */
    protected $rowBaseCalculator;

    public function testCalculateWithTaxInPrice()
    {
        $this->initMocks(true);
        $this->initRowBaseCalculator();
        $this->rowBaseCalculator->expects($this->atLeastOnce())
            ->method('deltaRound')->willReturn(0);

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->rowBaseCalculator, true)
        );
        $this->assertEqualsWithDelta(
            self::UNIT_PRICE_INCL_TAX_ROUNDED,
            $this->taxDetailsItem->getPriceInclTax(),
            self::EPSILON
        );

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->rowBaseCalculator, false)
        );
        $this->assertEqualsWithDelta(
            self::UNIT_PRICE_INCL_TAX,
            $this->taxDetailsItem->getPriceInclTax(),
            self::EPSILON
        );
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $this->initMocks(false);
        $this->initRowBaseCalculator();
        $this->rowBaseCalculator->expects($this->atLeastOnce())
            ->method('deltaRound');

        $this->assertSame(
            $this->taxDetailsItem,
            $this->calculate($this->rowBaseCalculator)
        );
    }

    private function initRowBaseCalculator()
    {
        $taxClassService = $this->getMockForAbstractClass(TaxClassManagementInterface::class);
        $this->rowBaseCalculator = $this->getMockBuilder(RowBaseCalculator::class)
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

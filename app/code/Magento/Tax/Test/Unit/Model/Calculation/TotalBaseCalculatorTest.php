<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;
use PHPUnit\Framework\MockObject\MockObject;

class TotalBaseCalculatorTest extends RowBaseAndTotalBaseCalculatorTestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /** @var MockObject */
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
        $this->assertEqualsWithDelta(
            self::UNIT_PRICE_INCL_TAX_ROUNDED,
            $this->taxDetailsItem->getPriceInclTax(),
            self::EPSILON
        );
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
        $this->assertEqualsWithDelta(
            self::UNIT_PRICE_INCL_TAX,
            $this->taxDetailsItem->getPriceInclTax(),
            self::EPSILON
        );
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
        $taxClassService = $this->getMockForAbstractClass(TaxClassManagementInterface::class);
        $this->totalBaseCalculator = $this->getMockBuilder(TotalBaseCalculator::class)
            ->onlyMethods(['deltaRound'])
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

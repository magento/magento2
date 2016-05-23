<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Weee\Test\Unit\Pricing;

use \Magento\Weee\Pricing\TaxAdjustment;


class TaxAdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxAdjustment
     */
    protected $adjustment;

    /**
     * @var \Magento\Weee\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelperMock;

    /**
     * @var \Magento\Tax\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var int
     */
    protected $sortOrder = 5;

    protected function setUp()
    {
        $this->weeeHelperMock = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $this->taxHelperMock = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->will($this->returnCallback(
                    function ($arg) {
                        return round($arg * 0.5, 2);
                    }
                )
            );
        $this->priceCurrencyMock->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(
                function ($arg) {
                    return $arg * 0.5;
                }
            )
            );

        $this->adjustment = new TaxAdjustment(
            $this->weeeHelperMock,
            $this->taxHelperMock,
            $this->priceCurrencyMock,
            $this->sortOrder
        );
    }

    public function testGetAdjustmentCode()
    {
        $this->assertEquals(TaxAdjustment::ADJUSTMENT_CODE, $this->adjustment->getAdjustmentCode());
    }

    public function testIsIncludedInBasePrice()
    {
        $this->assertFalse($this->adjustment->isIncludedInBasePrice());
    }

    /**
     * @param bool $taxDisplayExclTax
     * @param bool $isWeeeTaxable
     * @param bool $weeeDisplayConfig
     * @param bool $expectedResult
     * @dataProvider isIncludedInDisplayPriceDataProvider
     */
    public function testIsIncludedInDisplayPrice(
        $taxDisplayExclTax,
        $isWeeeTaxable,
        $weeeDisplayConfig,
        $expectedResult
    )
    {
        $this->weeeHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->weeeHelperMock->expects($this->any())
            ->method('isTaxable')
            ->willReturn($isWeeeTaxable);
        $this->taxHelperMock->expects($this->any())
            ->method('displayPriceExcludingTax')
            ->willReturn($taxDisplayExclTax);

        $displayTypes = [
            \Magento\Weee\Model\Tax::DISPLAY_EXCL,
        ];
        $this->weeeHelperMock->expects($this->any())
            ->method('typeOfDisplay')
            ->with($displayTypes)
            ->will($this->returnValue($weeeDisplayConfig));

        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInDisplayPrice());
    }

    /**
     * @return array
     */
    public function isIncludedInDisplayPriceDataProvider()
    {
        return [
            'display_incl_tax' => [
                'tax_display_excl_tax' => false,
                'is_weee_taxable' => true,
                'weee_display_config' => false,
                'expected_result' => true,
            ],
            'display_incl_tax_excl_weee' => [
                'tax_display_excl_tax' => false,
                'is_weee_taxable' => true,
                'weee_display_config' => true,
                'expected_result' => false,
            ],
            'display_excl_tax' => [
                'tax_display_excl_tax' => true,
                'is_weee_taxable' => true,
                'weee_display_config' => true,
                'expected_result' => false,
            ],
            'display_excl_tax_incl_weee' => [
                'tax_display_excl_tax' => true,
                'is_weee_taxable' => true,
                'weee_display_config' => false,
                'expected_result' => false,
            ],
        ];
    }

    /**
     * @param float $amount
     * @param \Magento\Framework\DataObject[] $weeeAttributes
     * @param float $expectedResult
     * @dataProvider applyAdjustmentDataProvider
     */
    public function testApplyAdjustment($amount, $weeeAttributes, $expectedResult)
    {
        $object = $this->getMockForAbstractClass('Magento\Framework\Pricing\SaleableInterface');

        $this->weeeHelperMock->expects($this->any())
            ->method('getProductWeeeAttributes')
            ->will($this->returnValue($weeeAttributes));

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public function applyAdjustmentDataProvider()
    {
        return [
            [
                'amount' => 10,
                'weee_attributes' => [
                    new \Magento\Framework\DataObject(
                        [
                            'tax_amount' => 5,
                        ]
                    ),
                    new \Magento\Framework\DataObject(
                        [
                            'tax_amount' => 2.5,
                        ]
                    ),

                ],
                'expected_result' => 13.75,
            ],
        ];
    }
}

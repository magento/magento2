<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Weee\Pricing;

use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Framework\Pricing\Object\SaleableInterface;

class AdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adjustment
     */
    protected $adjustment;

    /**
     * @var \Magento\Weee\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelper;

    /**
     * @var int
     */
    protected $sortOrder = 5;

    public function setUp()
    {
        $this->weeeHelper = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $this->adjustment = new Adjustment($this->weeeHelper, $this->sortOrder);
    }

    public function testGetAdjustmentCode()
    {
        $this->assertEquals(Adjustment::ADJUSTMENT_CODE, $this->adjustment->getAdjustmentCode());
    }

    public function testIsIncludedInBasePrice()
    {
        $this->assertFalse($this->adjustment->isIncludedInBasePrice());
    }

    /**
     * @dataProvider isIncludedInDisplayPriceDataProvider
     */
    public function testIsIncludedInDisplayPrice($expectedResult)
    {
        $displayTypes = [
            \Magento\Weee\Model\Tax::DISPLAY_INCL,
            \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR,
            \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL
        ];
        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with($displayTypes)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInDisplayPrice());
    }

    /**
     * @return array
     */
    public function isIncludedInDisplayPriceDataProvider()
    {
        return [[false], [true]];
    }

    /**
     * @param float $amount
     * @param float $expectedResult
     * @dataProvider extractAdjustmentDataProvider
     */
    public function testExtractAdjustment($amount, $expectedResult)
    {
        $saleableItem = $this->getMockForAbstractClass('Magento\Framework\Pricing\Object\SaleableInterface');

        $this->weeeHelper->expects($this->any())
            ->method('getAmount')
            ->with($saleableItem)
            ->will($this->returnValue($amount));

        $this->assertEquals($expectedResult, $this->adjustment->extractAdjustment($amount, $saleableItem));
    }

    /**
     * @return array
     */
    public function extractAdjustmentDataProvider()
    {
        return [
            [1.1, 1.1],
            [0.0, 0.0],
        ];
    }

    /**
     * @param float $amount
     * @param float $amountOld
     * @param float $expectedResult
     * @dataProvider applyAdjustmentDataProvider
     */
    public function testApplyAdjustment($amount, $amountOld, $expectedResult)
    {
        $object = $this->getMockForAbstractClass('Magento\Framework\Pricing\Object\SaleableInterface');

        $this->weeeHelper->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue($amountOld));

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public function applyAdjustmentDataProvider()
    {
        return [
            [1.1, 2.2, 3.3],
            [0.0, 2.2, 2.2],
            [1.1, 0.0, 1.1],
        ];
    }

    /**
     * @dataProvider isExcludedWithDataProvider
     * @param string $adjustmentCode
     * @param bool $expectedResult
     */
    public function testIsExcludedWith($adjustmentCode, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->adjustment->isExcludedWith($adjustmentCode));
    }

    public function isExcludedWithDataProvider()
    {
        return [
            ['weee', true],
            ['tax', true],
            ['not_tax_and_not_weee', false]
        ];
    }

    /**
     * @dataProvider getSortOrderProvider
     * @param bool $isTaxable
     * @param int $expectedResult
     */
    public function testGetSortOrder($isTaxable, $expectedResult)
    {
        $this->weeeHelper->expects($this->any())
            ->method('isTaxable')
            ->will($this->returnValue($isTaxable));

        $this->assertEquals($expectedResult, $this->adjustment->getSortOrder());
    }

    public function getSortOrderProvider()
    {
        return [
            [true, $this->sortOrder],
            [false, -1]
        ];
    }
}

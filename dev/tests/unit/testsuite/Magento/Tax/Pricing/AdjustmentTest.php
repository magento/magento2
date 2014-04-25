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

namespace Magento\Tax\Pricing;

use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\Pricing\Object\SaleableInterface;

class AdjustmentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAdjustmentCode()
    {
        // Instantiate/mock objects
        /** @var TaxHelper $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()
            ->setMethods(array())
            ->getMock();
        $model = new Adjustment($taxHelper);

        // Run tested method
        $code = $model->getAdjustmentCode();

        // Check expectations
        $this->assertNotEmpty($code);
    }

    /**
     * @param bool $isPriceIncludesTax
     * @dataProvider isIncludedInBasePriceDataProvider
     */
    public function testIsIncludedInBasePrice($isPriceIncludesTax)
    {
        // Instantiate/mock objects
        /** @var TaxHelper|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()
            ->setMethods(array('priceIncludesTax'))
            ->getMock();
        $model = new Adjustment($taxHelper);

        // Avoid execution of irrelevant functionality
        $taxHelper->expects($this->any())->method('priceIncludesTax')->will($this->returnValue($isPriceIncludesTax));

        // Run tested method
        $result = $model->isIncludedInBasePrice();

        // Check expectations
        $this->assertInternalType('bool', $result);
        $this->assertEquals($isPriceIncludesTax, $result);
    }

    public function isIncludedInBasePriceDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider isIncludedInDisplayPriceDataProvider
     */
    public function testIsIncludedInDisplayPrice($displayPriceIncludingTax, $displayBothPrices, $expectedResult)
    {
        // Instantiate/mock objects
        /** @var TaxHelper|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()
            ->setMethods(array('displayPriceIncludingTax', 'displayBothPrices'))
            ->getMock();

        // Avoid execution of irrelevant functionality
        $taxHelper->expects($this->any())
            ->method('displayPriceIncludingTax')
            ->will($this->returnValue($displayPriceIncludingTax));
        $taxHelper->expects($this->any())
            ->method('displayBothPrices')
            ->will($this->returnValue($displayBothPrices));

        $model = new Adjustment($taxHelper);
        // Run tested method
        $result = $model->isIncludedInDisplayPrice();

        // Check expectations
        $this->assertInternalType('bool', $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function isIncludedInDisplayPriceDataProvider()
    {
        return [
            [false, false, false],
            [false, true, true],
            [true, false, true],
            [true, true, true],
        ];
    }

    /**
     * @param float $amount
     * @param bool $isPriceIncludesTax
     * @param float $price
     * @param float $expectedResult
     * @dataProvider extractAdjustmentDataProvider
     */
    public function testExtractAdjustment($isPriceIncludesTax, $amount, $price, $expectedResult)
    {
        // Instantiate/mock objects
        /** @var TaxHelper|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()
            ->setMethods(array('priceIncludesTax', 'getPrice'))
            ->getMock();
        /** @var SaleableInterface|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $object = $this->getMockBuilder('Magento\Framework\Pricing\Object\SaleableInterface')->getMock();
        $model = new Adjustment($taxHelper);

                // Avoid execution of irrelevant functionality
        $taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->will($this->returnValue($isPriceIncludesTax));
        $taxHelper->expects($this->any())
            ->method('getPrice')
            ->with($object, $amount)
            ->will($this->returnValue($price));

        // Run tested method
        $result = $model->extractAdjustment($amount, $object);

        // Check expectations
        $this->assertInternalType('float', $result);
        $this->assertEquals($expectedResult, $result);
    }

    public function extractAdjustmentDataProvider()
    {
        return [
            [false, 'not_important', 'not_important', 0.00],
            [true, 10.1, 0.2, 9.9],
            [true, 10.1, 20.3, -10.2],
            [true, 0.0, 0.0, 0],
        ];
    }

    /**
     * @param bool $isPriceIncludesTax
     * @param float $amount
     * @param float $price
     * @param $expectedResult
     * @dataProvider applyAdjustmentDataProvider
     */
    public function testApplyAdjustment($isPriceIncludesTax, $amount, $price, $expectedResult)
    {
        // Instantiate/mock objects
        /** @var TaxHelper|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()
            ->setMethods(array('priceIncludesTax', 'getPrice'))
            ->getMock();
        /** @var SaleableInterface|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $object = $this->getMockBuilder('Magento\Framework\Pricing\Object\SaleableInterface')->getMock();
        $model = new Adjustment($taxHelper);

        // Avoid execution of irrelevant functionality
        $taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->will($this->returnValue($isPriceIncludesTax));
        $taxHelper->expects($this->any())
            ->method('getPrice')
            ->with($object, $amount, !$isPriceIncludesTax)
            ->will($this->returnValue($price));

        // Run tested method
        $result = $model->applyAdjustment($amount, $object);

        // Check expectations
        $this->assertInternalType('float', $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function applyAdjustmentDataProvider()
    {
        return [
            [true, 1.1, 2.2, 2.2],
            [true, 0.0, 2.2, 2.2],
            [true, 1.1, 0.0, 0.0],
        ];
    }

    public function testIsExcludedWith()
    {
        $adjustmentCode = 'some_random_adjustment_code123';

        // Instantiate/mock objects
        /** @var TaxHelper|\PHPUnit_Framework_MockObject_MockObject $taxHelper */
        $taxHelper = $this->getMockBuilder('Magento\Tax\Helper\Data')->disableOriginalConstructor()->getMock();
        $model = new Adjustment($taxHelper);

        // Run tested method
        $result = $model->isExcludedWith($adjustmentCode);

        // Check expectations
        $this->assertInternalType('bool', $result);
        $this->assertFalse($result);
    }
}

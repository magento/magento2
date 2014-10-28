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

namespace Magento\Bundle\Pricing\Price;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class FinalPriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Bundle\Pricing\Price\FinalPrice */
    protected $finalPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $saleableInterfaceMock;

    /** @var float */
    protected $quantity = 1.;

    /** @var float*/
    protected $baseAmount;

    /** @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleCalculatorMock;

    /** @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject */
    protected $priceInfoMock;

    /** @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $basePriceMock;

    /** @var BundleOptionPrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $bundleOptionMock;

    /**
     * @return void
     */
    protected function prepareMock()
    {
        $this->saleableInterfaceMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->bundleCalculatorMock = $this->getMock('Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface');

        $this->basePriceMock = $this->getMock('Magento\Catalog\Pricing\Price\BasePrice', [], [], '', false);
        $this->basePriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($this->baseAmount));

        $this->bundleOptionMock = $this->getMockBuilder('Magento\Bundle\Pricing\Price\BundleOptionPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfoMock = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);

        $this->priceInfoMock->expects($this->atLeastOnce())
            ->method('getPrice')
            ->will($this->returnValueMap([
                [\Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE, $this->basePriceMock],
                [BundleOptionPrice::PRICE_CODE, $this->bundleOptionMock]
            ]));

        $this->saleableInterfaceMock->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->finalPrice = new \Magento\Bundle\Pricing\Price\FinalPrice(
            $this->saleableInterfaceMock,
            $this->quantity,
            $this->bundleCalculatorMock
        );
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($baseAmount, $optionsValue, $result)
    {
        $this->baseAmount = $baseAmount;
        $this->prepareMock();
        $this->bundleOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($optionsValue));

        $this->assertSame($result, $this->finalPrice->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            [false, false, 0],
            [0, 1.2, 1.2],
            [1, 2, 3]
        ];
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetMaximalPrice($baseAmount)
    {
        $result = rand(1, 10);
        $this->baseAmount = $baseAmount;
        $this->prepareMock();

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getMaxAmount')
            ->with($this->equalTo($this->baseAmount), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->finalPrice->getMaximalPrice());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetMinimalPrice($baseAmount)
    {
        $result = rand(1, 10);
        $this->baseAmount = $baseAmount;
        $this->prepareMock();

        $this->bundleCalculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->equalTo($this->baseAmount), $this->equalTo($this->saleableInterfaceMock))
            ->will($this->returnValue($result));
        $this->assertSame($result, $this->finalPrice->getMinimalPrice());
    }
}

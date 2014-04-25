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
use Magento\Catalog\Pricing\Price as CatalogPrice;

/**
 * Class BundleSelectionPriceTest
 *
 * @package Magento\Bundle\Pricing\Price
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleSelectionPriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice */
    protected $bundleSelectionPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $saleableInterfaceMock;

    /** @var float */
    protected $quantity = 1.;

    /** @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $calculatorInterfaceMock;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Framework\Pricing\PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $priceInfoMock;

    /** @var \Magento\Catalog\Pricing\Price\BasePrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $basePriceMock;

    /** @var \Magento\Catalog\Pricing\Price\FinalPrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $finalPriceMock;

    /** @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit_Framework_MockObject_MockObject */
    protected $regularPriceMock;

    /** @var  float */
    protected $finalPriceValue;

    /** @var  float */
    protected $regularPriceValue;

    /** @var  float */
    protected $expectedResult;

    protected function prepare()
    {
        $this->saleableInterfaceMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            [
                'getPriceInfo',
                'getSelectionPriceType',
                'getSelectionPriceValue',
                '__wakeup',
                '__sleep'
            ],
            [],
            '',
            false
        );
        $this->calculatorInterfaceMock = $this->getMock('Magento\Framework\Pricing\Adjustment\CalculatorInterface');
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getPriceType', 'setFinalPrice', 'getQty', 'getData', 'getPriceInfo', '__wakeup', '__sleep'],
            [],
            '',
            false,
            false
        );

        $this->managerInterfaceMock = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $this->priceInfoMock = $this->getMock('\Magento\Framework\Pricing\PriceInfoInterface');
        $this->priceInfoMock->expects($this->atLeastOnce())
            ->method('getPrice')
            ->will($this->returnCallback(array($this, 'getPriceCallback')));

        $this->productMock->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));

        $this->saleableInterfaceMock->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
    }

    /**
     * @param string $priceType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getPriceCallback($priceType)
    {
        switch ($priceType) {
            case CatalogPrice\BasePrice::PRICE_TYPE_BASE_PRICE:
                $this->basePriceMock = $this->getMock('Magento\Bundle\Pricing\Price\BasePrice', [], [], '', false);
                $this->basePriceMock->expects($this->once())
                    ->method('applyDiscount')
                    ->with($this->expectedResult)
                    ->will($this->returnArgument(0));
                return $this->basePriceMock;
            case CatalogPrice\FinalPriceInterface::PRICE_TYPE_FINAL:
                $this->finalPriceMock = $this->getMock(
                    'Magento\Catalog\Pricing\Price\FinalPrice',
                    [],
                    [],
                    '',
                    false
                );
                $this->finalPriceMock->expects($this->once())
                    ->method('getValue')
                    ->will($this->returnValue($this->finalPriceValue));
                return $this->finalPriceMock;
            case CatalogPrice\RegularPrice::PRICE_TYPE_PRICE_DEFAULT:
                $this->regularPriceMock = $this->getMock(
                    'Magento\Catalog\Pricing\Price\RegularPrice',
                    [],
                    [],
                    '',
                    false
                );
                $this->regularPriceMock->expects($this->once())
                    ->method('getValue')
                    ->will($this->returnValue($this->regularPriceValue));
                return $this->regularPriceMock;
            default:
                break;
        }
        $this->fail('Price mock was not found');
    }

    /**
     * @param string $bundlePriceType
     * @param array $selectionData
     * @param float $expectedResult
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($bundlePriceType, $selectionData, $expectedResult)
    {
        $this->prepare();
        $this->expectedResult = $expectedResult;
        $this->productMock->expects($this->once())
            ->method('getPriceType')
            ->will($this->returnValue($bundlePriceType));

        if (isset($selectionData['getSelectionPriceType'])) {
            $this->saleableInterfaceMock->expects($this->once())
                ->method('getSelectionPriceType')
                ->will($this->returnValue($selectionData['getSelectionPriceType']));
            if ($selectionData['getSelectionPriceType']) {
                $this->regularPriceValue = $selectionData['regularPriceValue'];
                $this->productMock->expects($this->once())
                    ->method('setFinalPrice')
                    ->with($this->equalTo($selectionData['regularPriceValue']))
                    ->will($this->returnSelf());
                $this->productMock->expects($this->once())
                    ->method('getQty')
                    ->will($this->returnValue($selectionData['bundleQty']));

                $this->productMock->expects($this->once())
                    ->method('getData')
                    ->with($this->equalTo('final_price'))
                    ->will($this->returnValue($selectionData['finalPrice']));

                $this->managerInterfaceMock->expects($this->once())
                    ->method('dispatch')
                    ->with(
                        $this->equalTo('catalog_product_get_final_price'),
                        $this->equalTo(['product' => $this->productMock, 'qty' => $selectionData['bundleQty']])
                    )
                    ->will($this->returnSelf());
            }
            $this->quantity = $selectionData['bundleQty'];
            $this->saleableInterfaceMock->expects($this->once())
            ->method('getSelectionPriceValue')
            ->will($this->returnValue($selectionData['selectionPriceValue']));
        } else {
            $this->finalPriceValue = $expectedResult;
        }
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleSelectionPrice = $this->objectManagerHelper->getObject(
            'Magento\Bundle\Pricing\Price\BundleSelectionPrice',
            [
                'salableItem' => $this->saleableInterfaceMock,
                'quantity' => $this->quantity,
                'calculator' => $this->calculatorInterfaceMock,
                'bundleProduct' => $this->productMock,
                'eventManager' => $this->managerInterfaceMock
            ]
        );

        $this->assertSame($expectedResult, $this->bundleSelectionPrice->getValue());
        // test value caching
        $this->assertSame($expectedResult, $this->bundleSelectionPrice->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            'dynamic bundle' => [
                'bundle type' => 0,
                'selection data' => [],
                'expected result' => 3.3
            ],
            'fixed bundle - percent price' => [
                'bundle type' => 1,
                'selection data' => [
                    'getSelectionPriceType' => true,
                    'regularPriceValue' => 4.,
                    'bundleQty' => 3.,
                    'finalPrice' => 100,
                    'selectionPriceValue' => 10.
                ],
                'expected result' => 10.
            ],
            'fixed bundle - fixed price' => [
                'bundle type' => 1,
                'selection data' => [
                    'getSelectionPriceType' => false,
                    'bundleQty' => 2.,
                    'selectionPriceValue' => 10.
                ],
                'expected result' => 20.
            ],
        ];
    }
}

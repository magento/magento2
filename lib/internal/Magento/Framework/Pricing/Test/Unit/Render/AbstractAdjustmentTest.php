<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Render;

use \Magento\Framework\Pricing\Render\AbstractAdjustment;

/**
 * Test class for \Magento\Framework\Pricing\Render\AbstractAdjustment
 */
class AbstractAdjustmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AbstractAdjustment | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    /**
     * @var array
     */
    protected $data;

    protected function setUp()
    {
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->data = ['argument_one' => 1];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructorArgs = $objectManager->getConstructArguments(
            \Magento\Framework\Pricing\Render\AbstractAdjustment::class,
            [
                'priceCurrency' => $this->priceCurrency,
                'data' => $this->data
            ]
        );
        $this->model = $this->getMockBuilder(\Magento\Framework\Pricing\Render\AbstractAdjustment::class)
            ->setConstructorArgs($constructorArgs)
            ->setMethods(['getData', 'setData', 'apply'])
            ->getMockForAbstractClass();
    }

    public function testConvertAndFormatCurrency()
    {
        $amount = '100';
        $includeContainer = true;
        $precision = \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION;

        $result = '100.0 grn';

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($amount, $includeContainer, $precision)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->convertAndFormatCurrency($amount, $includeContainer, $precision));
    }

    public function testRender()
    {
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $arguments = ['argument_two' => 2];
        $mergedArguments = ['argument_one' => 1, 'argument_two' => 2];
        $renderText = 'amount data';

        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->expects($this->at(1))
            ->method('setData')
            ->with($mergedArguments);
        $this->model->expects($this->at(2))
            ->method('apply')
            ->will($this->returnValue($renderText));
        $this->model->expects($this->at(3))
            ->method('setData')
            ->with($this->data);

        $result = $this->model->render($amountRender, $arguments);
        $this->assertEquals($renderText, $result);
    }

    public function testGetAmountRender()
    {
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->render($amountRender);
        $this->assertEquals($amountRender, $this->model->getAmountRender());
    }

    public function testGetPriceType()
    {
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $price = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $sealableItem = $this->getMockForAbstractClass(\Magento\Framework\Pricing\SaleableInterface::class);
        $priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $priceCode = 'regular_price';

        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->will($this->returnValue($sealableItem));
        $sealableItem->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));
        $priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($priceCode)
            ->will($this->returnValue($price));

        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->render($amountRender);
        $this->assertEquals($price, $this->model->getPriceType($priceCode));
    }

    public function testGetPrice()
    {
        $price = 100;
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $amountRender->expects($this->once())
            ->method('getPrice')
            ->with()
            ->will($this->returnValue($price));

        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->render($amountRender);
        $this->assertEquals($price, $this->model->getPrice());
    }

    public function testGetSealableItem()
    {
        $sealableItem = $this->getMockForAbstractClass(\Magento\Framework\Pricing\SaleableInterface::class);
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->with()
            ->will($this->returnValue($sealableItem));

        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->render($amountRender);
        $this->assertEquals($sealableItem, $this->model->getSaleableItem());
    }

    public function testGetAdjustment()
    {
        $amountRender = $this->createMock(\Magento\Framework\Pricing\Render\Amount::class);
        $adjustment = $this->getMockForAbstractClass(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class);
        $sealableItem = $this->getMockForAbstractClass(\Magento\Framework\Pricing\SaleableInterface::class);
        $priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $adjustmentCode = 'tax';

        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->will($this->returnValue($sealableItem));
        $sealableItem->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));
        $priceInfo->expects($this->once())
            ->method('getAdjustment')
            ->with($adjustmentCode)
            ->will($this->returnValue($adjustment));

        $this->model->expects($this->at(0))
            ->method('getData')
            ->will($this->returnValue($this->data));
        $this->model->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($adjustmentCode));
        $this->model->render($amountRender);
        $this->assertEquals($adjustment, $this->model->getAdjustment());
    }

    public function testFormatCurrency()
    {
        $amount = 5.3456;
        $includeContainer = false;
        $precision = 3;

        $expected = 5.346;

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($amount, $includeContainer, $precision)
            ->will($this->returnValue($expected));

        $result = $this->model->formatCurrency($amount, $includeContainer, $precision);
        $this->assertEquals($expected, $result, 'formatCurrent returned unexpected result');
    }
}

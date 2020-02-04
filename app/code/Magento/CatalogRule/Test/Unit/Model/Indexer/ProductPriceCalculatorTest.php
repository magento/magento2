<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

class ProductPriceCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator
     */
    private $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator($this->priceCurrencyMock);
    }

    public function testCalculateToFixedPrice()
    {
        $rulePrice = 100;
        $actionAmount = 50;
        $ruleData = [
            'action_operator' => 'to_fixed',
            'action_amount' => $actionAmount
        ];
        $productData = ['rule_price' => $rulePrice];

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($actionAmount)
            ->willReturn($actionAmount);

        $this->assertEquals($actionAmount, $this->model->calculate($ruleData, $productData));
    }

    public function testCalculateToPercentPrice()
    {
        $rulePrice = 200;
        $actionAmount = 50;
        $expectedPrice = 100;
        $ruleData = [
            'action_operator' => 'to_percent',
            'action_amount' => $actionAmount
        ];
        $productData = ['rule_price' => $rulePrice];

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($expectedPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->model->calculate($ruleData, $productData));
    }

    public function testCalculateByFixedPrice()
    {
        $rulePrice = 200;
        $actionAmount = 50;
        $expectedPrice = 150;
        $ruleData = [
            'action_operator' => 'by_fixed',
            'action_amount' => $actionAmount
        ];
        $productData = ['rule_price' => $rulePrice];

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($expectedPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->model->calculate($ruleData, $productData));
    }

    public function testCalculateByPercentPrice()
    {
        $rulePrice = 200;
        $actionAmount = 50;
        $expectedPrice = 100;
        $ruleData = [
            'action_operator' => 'by_percent',
            'action_amount' => $actionAmount
        ];
        $productData = ['rule_price' => $rulePrice];

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($expectedPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->model->calculate($ruleData, $productData));
    }
}

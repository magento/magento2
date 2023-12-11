<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductPriceCalculatorTest extends TestCase
{
    /**
     * @var ProductPriceCalculator
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = new ProductPriceCalculator($this->priceCurrencyMock);
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

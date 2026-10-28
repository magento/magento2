<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Bundle\Pricing\Adjustment\Calculator;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Bundle\Pricing\Price\BundleOptions;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BundleOptionPriceTest extends TestCase
{
    /**
     * @var BundleOptionPrice
     */
    private $bundleOptionPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItemMock;

    /**
     * @var BundleCalculatorInterface|MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var BundleOptions|MockObject
     */
    private $bundleOptionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->bundleOptionsMock = $this->createMock(BundleOptions::class);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->bundleCalculatorMock = $this->createMock(Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionPrice = $this->objectManagerHelper->getObject(
            BundleOptionPrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
                'bundleOptions' => $this->bundleOptionsMock,
            ]
        );
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getOptions
     *
     * @return void
     */
    public function testGetOptions()
    {
        $collection = $this->createMock(Collection::class);
        $this->bundleOptionsMock->method('getOptions')->willReturn($collection);
        $this->assertEquals($collection, $this->bundleOptionPrice->getOptions());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getOptionSelectionAmount
     *
     * @return void
     */
    public function testGetOptionSelectionAmount()
    {
        $selectionAmount = $this->createAmountInterfaceMock();
        $product = $this->createMock(Product::class);
        $selection = $this->createMock(Selection::class);
        $this->bundleOptionsMock->method('getOptionSelectionAmount')->willReturn($selectionAmount)
            ->with($product, $selection, false);
        $this->assertEquals($selectionAmount, $this->bundleOptionPrice->getOptionSelectionAmount($selection));
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getAmount
     *
     * @return void
     */
    public function testGetAmount()
    {
        $amountMock = $this->createAmountInterfaceMock();
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItemMock)
            ->willReturn($amountMock);
        $this->assertSame($amountMock, $this->bundleOptionPrice->getAmount());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getValue
     *
     * @return void
     */
    public function testGetValue()
    {
        $value = 1.0;
        $this->bundleOptionsMock->method('calculateOptions')->willReturn($value);
        $this->assertEquals($value, $this->bundleOptionPrice->getValue());
    }

    /**
     * Create a mock that implements all AmountInterface abstract methods
     *
     * @return AmountInterface
     */
    private function createAmountInterfaceMock(): AmountInterface
    {
        $mock = $this->createMock(AmountInterface::class);
        
        // Mock all abstract methods with default values
        $mock->method('__toString')->willReturn('0');
        $mock->method('getAdjustmentAmount')->willReturn(0.0);
        $mock->method('getTotalAdjustmentAmount')->willReturn(0.0);
        $mock->method('getAdjustmentAmounts')->willReturn([]);
        $mock->method('hasAdjustment')->willReturn(false);
        
        return $mock;
    }
}

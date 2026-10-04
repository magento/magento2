<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ConfigurableMaxPriceCalculator;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ConfigurableRegularPrice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableRegularPriceTest extends TestCase
{
    /**
     * @var ConfigurableRegularPrice
     */
    private $model;

    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItemMock;

    /**
     * @var CalculatorInterface|MockObject
     */
    private $calculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var PriceResolverInterface|MockObject
     */
    private $priceResolverMock;

    /**
     * @var ConfigurableMaxPriceCalculator|MockObject
     */
    private $configurableMaxPriceCalculatorMock;

    /**
     * @var LowestPriceOptionsProviderInterface|MockObject
     */
    private $lowestPriceOptionsProviderMock;

    /**
     * @var ConfigurableOptionsProviderInterface|MockObject
     */
    private $configurableOptionsProviderMock;

    /**
     * @var float
     */
    private $quantity = 1.0;

    protected function setUp(): void
    {
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->calculatorMock = $this->createMock(CalculatorInterface::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->priceResolverMock = $this->createMock(PriceResolverInterface::class);
        $this->configurableMaxPriceCalculatorMock = $this->createMock(ConfigurableMaxPriceCalculator::class);
        $this->lowestPriceOptionsProviderMock = $this->createMock(LowestPriceOptionsProviderInterface::class);
        $this->configurableOptionsProviderMock = $this->createMock(ConfigurableOptionsProviderInterface::class);

        $this->model = new ConfigurableRegularPrice(
            $this->saleableItemMock,
            $this->quantity,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->priceResolverMock,
            $this->configurableMaxPriceCalculatorMock,
            $this->lowestPriceOptionsProviderMock
        );
    }

    /**
     * Test constructor sets dependencies correctly
     */
    public function testConstructor(): void
    {
        $model = new ConfigurableRegularPrice(
            $this->saleableItemMock,
            $this->quantity,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->priceResolverMock,
            $this->configurableMaxPriceCalculatorMock,
            $this->lowestPriceOptionsProviderMock
        );

        $this->assertInstanceOf(ConfigurableRegularPrice::class, $model);
    }

    /**
     * Test constructor with null lowestPriceOptionsProvider uses ObjectManager fallback
     */
    public function testConstructorWithNullLowestPriceOptionsProviderObjectManager(): void
    {
        // Mock the ObjectManager to test the fallback path
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $mockLowestPriceOptionsProvider = $this->createMock(LowestPriceOptionsProviderInterface::class);

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(LowestPriceOptionsProviderInterface::class)
            ->willReturn($mockLowestPriceOptionsProvider);

        // Set the ObjectManager instance
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // This test covers the ObjectManager fallback path in the constructor
        // when $lowestPriceOptionsProvider is null
        $model = new ConfigurableRegularPrice(
            $this->saleableItemMock,
            $this->quantity,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->priceResolverMock,
            $this->configurableMaxPriceCalculatorMock,
            null // This triggers the ObjectManager::getInstance()->get() path
        );

        $this->assertInstanceOf(ConfigurableRegularPrice::class, $model);

        // Verify the ObjectManager was called and the dependency was set
        $reflection = new \ReflectionClass($model);
        $lowestPriceOptionsProviderProperty = $reflection->getProperty('lowestPriceOptionsProvider');
        $this->assertSame($mockLowestPriceOptionsProvider, $lowestPriceOptionsProviderProperty->getValue($model));
    }

    /**
     * Test getValue method returns cached value
     */
    public function testGetValueReturnsCachedValue(): void
    {
        $productId = 123;
        $expectedPrice = 99.99;

        $this->saleableItemMock->expects($this->atLeast(2))
            ->method('getId')
            ->willReturn($productId);

        $this->priceResolverMock->expects($this->once())
            ->method('resolvePrice')
            ->with($this->saleableItemMock)
            ->willReturn($expectedPrice);

        // First call should resolve price
        $result1 = $this->model->getValue();
        $this->assertEquals($expectedPrice, $result1);

        // Second call should return cached value
        $result2 = $this->model->getValue();
        $this->assertEquals($expectedPrice, $result2);
    }

    /**
     * Test getAmount returns min regular amount
     */
    public function testGetAmountReturnsMinRegularAmount(): void
    {
        $minAmountMock = $this->createMock(AmountInterface::class);
        $childProductMock = $this->createMock(Product::class);
        $priceInfoMock = $this->createMock(PriceInfoInterface::class);
        $priceMock = $this->createMock(PriceInterface::class);

        $this->lowestPriceOptionsProviderMock->expects($this->once())
            ->method('getProducts')
            ->with($this->saleableItemMock)
            ->willReturn([$childProductMock]);

        $childProductMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($priceMock);

        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($minAmountMock);

        $result = $this->model->getAmount();
        $this->assertSame($minAmountMock, $result);
    }

    /**
     * Test getMinRegularAmount with multiple products
     */
    public function testGetMinRegularAmountWithMultipleProducts(): void
    {
        $product1Mock = $this->createMock(Product::class);
        $product2Mock = $this->createMock(Product::class);
        $priceInfo1Mock = $this->createMock(PriceInfoInterface::class);
        $priceInfo2Mock = $this->createMock(PriceInfoInterface::class);
        $price1Mock = $this->createMock(PriceInterface::class);
        $price2Mock = $this->createMock(PriceInterface::class);
        $amount1Mock = $this->createMock(AmountInterface::class);
        $amount2Mock = $this->createMock(AmountInterface::class);

        $amount1Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(50.0);

        $amount2Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(30.0); // Lower price

        $this->lowestPriceOptionsProviderMock->expects($this->once())
            ->method('getProducts')
            ->with($this->saleableItemMock)
            ->willReturn([$product1Mock, $product2Mock]);

        $product1Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo1Mock);

        $product2Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo2Mock);

        $priceInfo1Mock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($price1Mock);

        $priceInfo2Mock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($price2Mock);

        $price1Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount1Mock);

        $price2Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount2Mock);

        $result = $this->model->getMinRegularAmount();
        $this->assertSame($amount2Mock, $result);
    }

    /**
     * Test getMaxRegularAmount with multiple products
     */
    public function testGetMaxRegularAmountWithMultipleProducts(): void
    {
        $product1Mock = $this->createMock(Product::class);
        $product2Mock = $this->createMock(Product::class);
        $priceInfo1Mock = $this->createMock(PriceInfoInterface::class);
        $priceInfo2Mock = $this->createMock(PriceInfoInterface::class);
        $price1Mock = $this->createMock(PriceInterface::class);
        $price2Mock = $this->createMock(PriceInterface::class);
        $amount1Mock = $this->createMock(AmountInterface::class);
        $amount2Mock = $this->createMock(AmountInterface::class);

        $amount1Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(50.0);

        $amount2Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(80.0); // Higher price

        // Mock the getUsedProducts method through reflection
        $reflection = new \ReflectionClass($this->model);
        $reflection->getMethod('getUsedProducts');
        // We need to mock the configurable options provider
        $configurableOptionsProviderProperty = $reflection->getProperty('configurableOptionsProvider');
        $configurableOptionsProviderProperty->setValue($this->model, $this->configurableOptionsProviderMock);

        $this->configurableOptionsProviderMock->expects($this->once())
            ->method('getProducts')
            ->with($this->saleableItemMock)
            ->willReturn([$product1Mock, $product2Mock]);

        $product1Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo1Mock);

        $product2Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo2Mock);

        $priceInfo1Mock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($price1Mock);

        $priceInfo2Mock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($price2Mock);

        $price1Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount1Mock);

        $price2Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount2Mock);

        $result = $this->model->getMaxRegularAmount();
        $this->assertSame($amount2Mock, $result);
    }

    /**
     * Test getMaxRegularAmount returns cached value on second call
     */
    public function testGetMaxRegularAmountReturnsCachedValue(): void
    {
        $productMock = $this->createMock(Product::class);
        $priceInfoMock = $this->createMock(PriceInfoInterface::class);
        $priceMock = $this->createMock(PriceInterface::class);
        $amountMock = $this->createMock(AmountInterface::class);

        $amountMock->method('getValue')
            ->willReturn(100.0);

        // Mock the configurable options provider
        $reflection = new \ReflectionClass($this->model);
        $configurableOptionsProviderProperty = $reflection->getProperty('configurableOptionsProvider');
        $configurableOptionsProviderProperty->setValue($this->model, $this->configurableOptionsProviderMock);

        $this->configurableOptionsProviderMock->expects($this->once()) // Should only be called once
        ->method('getProducts')
            ->with($this->saleableItemMock)
            ->willReturn([$productMock]);

        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($priceMock);

        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);

        // First call
        $result1 = $this->model->getMaxRegularAmount();
        $this->assertSame($amountMock, $result1);

        // Second call should return cached value
        $result2 = $this->model->getMaxRegularAmount();
        $this->assertSame($amountMock, $result2);
    }

    /**
     * Test _resetState clears caches
     */
    public function testResetStateClearsCaches(): void
    {
        $productId = 123;
        $expectedPrice = 99.99;

        // Set up initial cached values
        $this->saleableItemMock->expects($this->atLeast(3))
            ->method('getId')
            ->willReturn($productId);

        $this->priceResolverMock->expects($this->exactly(2))
            ->method('resolvePrice')
            ->with($this->saleableItemMock)
            ->willReturn($expectedPrice);

        // First call caches the value
        $this->model->getValue();

        // Reset state
        $this->model->_resetState();

        // After reset, getValue should call priceResolver again
        $result = $this->model->getValue();
        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Test isChildProductsOfEqualPrices with cache hit
     */
    public function testIsChildProductsOfEqualPricesWithCacheHit(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        // First call to cache the result
        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, null],
                ['max_price', null, null]
            ]);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', false);

        $typeInstanceMock = $this->createMock(Configurable::class);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $typeInstanceMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($productMock)
            ->willReturn([]);

        $result1 = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertFalse($result1);

        // Second call should use cache
        $result2 = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertFalse($result2);
    }

    /**
     * Test isChildProductsOfEqualPrices with indexed data
     */
    public function testIsChildProductsOfEqualPricesWithIndexedData(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        // Mock indexed data
        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, '50.00'],
                ['max_price', null, '50.00']
            ]);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', true);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertTrue($result);
    }

    /**
     * Test isChildProductsOfEqualPrices with different indexed prices
     */
    public function testIsChildProductsOfEqualPricesWithDifferentIndexedPrices(): void
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        // Mock different indexed prices
        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, '50.00'],
                ['max_price', null, '80.00']
            ]);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', false);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertFalse($result);
    }

    /**
     * Test isChildProductsOfEqualPrices with equal child prices
     */
    public function testIsChildProductsOfEqualPricesWithEqualChildPrices(): void
    {
        $productMock = $this->createMock(Product::class);
        $child1Mock = $this->createMock(Product::class);
        $child2Mock = $this->createMock(Product::class);
        $priceInfo1Mock = $this->createMock(PriceInfoInterface::class);
        $priceInfo2Mock = $this->createMock(PriceInfoInterface::class);
        $finalPrice1Mock = $this->createMock(PriceInterface::class);
        $finalPrice2Mock = $this->createMock(PriceInterface::class);
        $amount1Mock = $this->createMock(AmountInterface::class);
        $amount2Mock = $this->createMock(AmountInterface::class);

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, null],
                ['max_price', null, null]
            ]);

        $typeInstanceMock = $this->createMock(Configurable::class);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $typeInstanceMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($productMock)
            ->willReturn([$child1Mock, $child2Mock]);

        // Both children are salable
        $child1Mock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        $child2Mock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        // Mock price info for children
        $child1Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo1Mock);

        $child2Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo2Mock);

        $priceInfo1Mock->expects($this->once())
            ->method('getPrice')
            ->with('final_price')
            ->willReturn($finalPrice1Mock);

        $priceInfo2Mock->expects($this->once())
            ->method('getPrice')
            ->with('final_price')
            ->willReturn($finalPrice2Mock);

        $finalPrice1Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount1Mock);

        $finalPrice2Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount2Mock);

        // Same prices
        $amount1Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(50.0);

        $amount2Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(50.0);

        // Mock parent final price
        $productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn(50.0);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', true);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertTrue($result);
    }

    /**
     * Test isChildProductsOfEqualPrices with different child prices
     */
    public function testIsChildProductsOfEqualPricesWithDifferentChildPrices(): void
    {
        $productMock = $this->createMock(Product::class);
        $child1Mock = $this->createMock(Product::class);
        $child2Mock = $this->createMock(Product::class);
        $priceInfo1Mock = $this->createMock(PriceInfoInterface::class);
        $priceInfo2Mock = $this->createMock(PriceInfoInterface::class);
        $finalPrice1Mock = $this->createMock(PriceInterface::class);
        $finalPrice2Mock = $this->createMock(PriceInterface::class);
        $amount1Mock = $this->createMock(AmountInterface::class);
        $amount2Mock = $this->createMock(AmountInterface::class);

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, null],
                ['max_price', null, null]
            ]);

        $typeInstanceMock = $this->createMock(Configurable::class);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $typeInstanceMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($productMock)
            ->willReturn([$child1Mock, $child2Mock]);

        // Both children are salable
        $child1Mock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        $child2Mock->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        // Mock price info for children
        $child1Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo1Mock);

        $child2Mock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo2Mock);

        $priceInfo1Mock->expects($this->once())
            ->method('getPrice')
            ->with('final_price')
            ->willReturn($finalPrice1Mock);

        $priceInfo2Mock->expects($this->once())
            ->method('getPrice')
            ->with('final_price')
            ->willReturn($finalPrice2Mock);

        $finalPrice1Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount1Mock);

        $finalPrice2Mock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amount2Mock);

        // Different prices
        $amount1Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(50.0);

        $amount2Mock->expects($this->once())
            ->method('getValue')
            ->willReturn(80.0);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', false);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertFalse($result);
    }

    /**
     * Test isChildProductsOfEqualPrices with no saleable children
     */
    public function testIsChildProductsOfEqualPricesWithNoSaleableChildren(): void
    {
        $productMock = $this->createMock(Product::class);
        $childMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $productMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->willReturn(1);

        $productMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['_children_final_prices_equal_store_1', null, null],
                ['minimal_price', null, null],
                ['max_price', null, null]
            ]);

        $typeInstanceMock = $this->createMock(Configurable::class);
        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $typeInstanceMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($productMock)
            ->willReturn([$childMock]);

        // Child is not salable
        $childMock->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);

        $productMock->expects($this->once())
            ->method('setData')
            ->with('_children_final_prices_equal_store_1', false);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertFalse($result);
    }

    /**
     * Test isChildProductsOfEqualPrices with memoized result
     */
    public function testIsChildProductsOfEqualPricesWithMemoizedResult(): void
    {
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('getData')
            ->with('_children_final_prices_equal_store_0')
            ->willReturn(true);

        $result = $this->model->isChildProductsOfEqualPrices($productMock);
        $this->assertTrue($result);
    }

    /**
     * Test getConfigurableOptionsProvider method through getMaxRegularAmount call
     */
    public function testGetConfigurableOptionsProviderThroughMaxRegularAmount(): void
    {
        // This test covers the getConfigurableOptionsProvider method by calling it indirectly
        // through getMaxRegularAmount, which calls getUsedProducts, which calls getConfigurableOptionsProvider

        $productMock = $this->createMock(Product::class);
        $priceInfoMock = $this->createMock(PriceInfoInterface::class);
        $priceMock = $this->createMock(PriceInterface::class);
        $amountMock = $this->createMock(AmountInterface::class);

        $amountMock->method('getValue')->willReturn(100.0);

        // Create a mock provider to avoid ObjectManager call
        $mockProvider = $this->createMock(ConfigurableOptionsProviderInterface::class);
        $mockProvider->expects($this->once())
            ->method('getProducts')
            ->with($this->saleableItemMock)
            ->willReturn([$productMock]);

        // Use reflection to pre-set the configurableOptionsProvider to our mock
        $reflection = new \ReflectionClass($this->model);
        $configurableOptionsProviderProperty = $reflection->getProperty('configurableOptionsProvider');
        $configurableOptionsProviderProperty->setValue($this->model, $mockProvider);

        // Set up the product mock chain
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableRegularPrice::PRICE_CODE)
            ->willReturn($priceMock);

        $priceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($amountMock);

        // Call getMaxRegularAmount which will internally call:
        // getMaxRegularAmount -> doGetMaxRegularAmount -> getUsedProducts -> getConfigurableOptionsProvider
        $result = $this->model->getMaxRegularAmount();

        // Verify we got the expected result and the method was called
        $this->assertSame($amountMock, $result);

        // Verify the configurableOptionsProvider was used (not null)
        $this->assertSame($mockProvider, $configurableOptionsProviderProperty->getValue($this->model));
    }

    /**
     * Test getConfigurableOptionsProvider lazy loading with ObjectManager
     */
    public function testGetConfigurableOptionsProviderObjectManagerLazyLoading(): void
    {
        // Mock the ObjectManager to test the lazy loading path
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $mockConfigurableOptionsProvider = $this->createMock(ConfigurableOptionsProviderInterface::class);

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(ConfigurableOptionsProviderInterface::class)
            ->willReturn($mockConfigurableOptionsProvider);

        // Set the ObjectManager instance
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Create a fresh model to ensure configurableOptionsProvider starts as null
        $freshModel = new ConfigurableRegularPrice(
            $this->saleableItemMock,
            $this->quantity,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->priceResolverMock,
            $this->configurableMaxPriceCalculatorMock,
            $this->lowestPriceOptionsProviderMock
        );

        $reflection = new \ReflectionClass($freshModel);
        $configurableOptionsProviderProperty = $reflection->getProperty('configurableOptionsProvider');
        // Verify it starts as null
        $this->assertNull($configurableOptionsProviderProperty->getValue($freshModel));

        // Access the private method to test the lazy loading
        $getConfigurableOptionsProviderMethod = $reflection->getMethod('getConfigurableOptionsProvider');

        // First call should trigger ObjectManager and set the property
        $result1 = $getConfigurableOptionsProviderMethod->invoke($freshModel);
        $this->assertSame($mockConfigurableOptionsProvider, $result1);

        // Verify the property was set by the lazy loading
        $this->assertSame(
            $mockConfigurableOptionsProvider,
            $configurableOptionsProviderProperty->getValue($freshModel)
        );

        // Second call should return the cached instance (ObjectManager should not be called again)
        $result2 = $getConfigurableOptionsProviderMethod->invoke($freshModel);
        $this->assertSame($mockConfigurableOptionsProvider, $result2);
    }
}

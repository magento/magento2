<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Test\Unit\Model\Resolver;

use Magento\BundleGraphQl\Model\Resolver\PriceRange;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\PriceRangeDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceRangeTest extends TestCase
{
    /**
     * @var PriceProviderPool|MockObject
     */
    private PriceProviderPool|MockObject $priceProviderPool;

    /**
     * @var Discount|MockObject
     */
    private Discount|MockObject $discount;

    /**
     * @var ProductDataProvider|MockObject
     */
    private ProductDataProvider|MockObject $productDataProvider;

    /**
     * @var PriceRangeDataProvider|MockObject
     */
    private PriceRangeDataProvider|MockObject $priceRangeDataProvider;

    /**
     * @var ValueFactory|MockObject
     */
    private ValueFactory|MockObject $valueFactory;

    /**
     * @var PriceRange
     */
    private PriceRange $resolver;

    /**
     * @var Field|MockObject
     */
    private Field|MockObject $field;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $context;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo|MockObject $info;

    protected function setUp(): void
    {
        $this->priceProviderPool = $this->createMock(PriceProviderPool::class);
        $this->discount = $this->createMock(Discount::class);
        $this->productDataProvider = $this->createMock(ProductDataProvider::class);
        $this->priceRangeDataProvider = $this->createMock(PriceRangeDataProvider::class);
        $this->valueFactory = $this->createMock(ValueFactory::class);
        $this->field = $this->createMock(Field::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->info = $this->createMock(ResolveInfo::class);

        $this->resolver = new PriceRange(
            $this->priceProviderPool,
            $this->discount,
            $this->productDataProvider,
            $this->priceRangeDataProvider,
            $this->valueFactory
        );
    }

    /**
     * Test that resolve() queues the SKU on the shared provider and returns a deferred Value.
     * Critically, getProductBySku must NOT be called during resolve() — only inside the deferred closure.
     */
    public function testResolveQueuesSKUAndReturnsDeferredValue(): void
    {
        $sku = 'bundle-child-001';

        $this->productDataProvider->expects($this->once())
            ->method('addProductSku')
            ->with($sku);

        // getProductBySku must NOT be called at resolve() time — only deferred
        $this->productDataProvider->expects($this->never())
            ->method('getProductBySku');

        $valueMock = $this->createMock(Value::class);
        $this->valueFactory->expects($this->once())
            ->method('create')
            ->with($this->isCallable())
            ->willReturn($valueMock);

        $result = $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            ['sku' => $sku]
        );

        $this->assertSame($valueMock, $result);
    }

    /**
     * Test that the deferred callback fetches product data and delegates to PriceRangeDataProvider.
     */
    public function testDeferredCallbackFetchesProductAndPreparesPrice(): void
    {
        $sku = 'bundle-child-002';
        $productMock = $this->createMock(Product::class);
        $expectedPriceData = [
            'minimum_price' => ['regular_price' => ['value' => 10.0]],
            'maximum_price' => ['regular_price' => ['value' => 20.0]],
        ];

        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->expects($this->once())
            ->method('getProductBySku')
            ->with($sku, $this->context)
            ->willReturn(['model' => $productMock]);

        $this->priceRangeDataProvider->expects($this->once())
            ->method('prepare')
            ->with(
                $this->context,
                $this->info,
                $this->callback(fn($v) => $v['model'] === $productMock && $v['sku'] === $sku)
            )
            ->willReturn($expectedPriceData);

        $capturedCallback = null;
        $this->valueFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (callable $callback) use (&$capturedCallback) {
                $capturedCallback = $callback;
                return $this->createMock(Value::class);
            });

        $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            ['sku' => $sku]
        );

        $this->assertNotNull($capturedCallback);
        $this->assertEquals($expectedPriceData, $capturedCallback());
    }

    /**
     * Test that the deferred callback returns null when product is not found.
     */
    public function testDeferredCallbackReturnsNullWhenProductNotFound(): void
    {
        $sku = 'bundle-child-nonexistent';

        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->expects($this->once())
            ->method('getProductBySku')
            ->with($sku, $this->context)
            ->willReturn([]);

        $this->priceRangeDataProvider->expects($this->never())
            ->method('prepare');

        $capturedCallback = null;
        $this->valueFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (callable $callback) use (&$capturedCallback) {
                $capturedCallback = $callback;
                return $this->createMock(Value::class);
            });

        $this->resolver->resolve(
            $this->field,
            $this->context,
            $this->info,
            ['sku' => $sku]
        );

        $this->assertNotNull($capturedCallback);
        $this->assertNull($capturedCallback());
    }

    /**
     * Test that multiple resolve() calls all queue on the shared singleton
     * without triggering any DB fetch during resolve().
     */
    public function testMultipleResolvesQueueSKUsWithoutEarlyFetch(): void
    {
        $skus = ['sku-x', 'sku-y', 'sku-z'];

        $this->productDataProvider->expects($this->exactly(3))
            ->method('addProductSku');

        $this->productDataProvider->expects($this->never())
            ->method('getProductBySku');

        $this->valueFactory->method('create')
            ->willReturn($this->createMock(Value::class));

        foreach ($skus as $sku) {
            $this->resolver->resolve(
                $this->field,
                $this->context,
                $this->info,
                ['sku' => $sku]
            );
        }
    }
}

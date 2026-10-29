<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Test\Unit\Model\Resolver\Options;

use Magento\BundleGraphQl\Model\Resolver\Options\Label;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    /**
     * @var ValueFactory|MockObject
     */
    private ValueFactory|MockObject $valueFactory;

    /**
     * @var ProductDataProvider|MockObject
     */
    private ProductDataProvider|MockObject $productDataProvider;

    /**
     * @var Label
     */
    private Label $resolver;

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
        $this->valueFactory = $this->createMock(ValueFactory::class);
        $this->productDataProvider = $this->createMock(ProductDataProvider::class);
        $this->field = $this->createMock(Field::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->info = $this->createMock(ResolveInfo::class);

        $this->resolver = new Label($this->valueFactory, $this->productDataProvider);
    }

    /**
     * Test that resolve() throws LocalizedException when 'sku' is missing from value.
     */
    public function testResolveThrowsExceptionWhenSkuMissing(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"sku" value should be specified');

        $this->resolver->resolve($this->field, $this->context, $this->info, []);
    }

    /**
     * Test that resolve() queues SKU and EAV attributes on the shared singleton,
     * then returns a deferred Value.
     */
    public function testResolveQueuesSKUOnSharedProviderAndReturnsDeferredValue(): void
    {
        $sku = 'test-sku-001';

        $this->productDataProvider->expects($this->once())
            ->method('addProductSku')
            ->with($sku);

        $this->productDataProvider->expects($this->once())
            ->method('addEavAttributes')
            ->with(['name']);

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
     * Test that the deferred callback returns the product name when found.
     */
    public function testDeferredCallbackReturnsProductName(): void
    {
        $sku = 'bundle-child-sku';
        $productName = 'Test Product Name';

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);

        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->method('addEavAttributes');
        $this->productDataProvider->expects($this->once())
            ->method('getProductBySku')
            ->with($sku, $this->context)
            ->willReturn(['model' => $productMock]);

        $capturedCallback = null;
        $this->valueFactory->expects($this->once())
            ->method('create')
            ->with($this->isCallable())
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
        $this->assertEquals($productName, $capturedCallback());
    }

    /**
     * Test that the deferred callback returns null when product is not found.
     */
    public function testDeferredCallbackReturnsNullWhenProductNotFound(): void
    {
        $sku = 'nonexistent-sku';

        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->method('addEavAttributes');
        $this->productDataProvider->expects($this->once())
            ->method('getProductBySku')
            ->with($sku, $this->context)
            ->willReturn([]);

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

        $this->assertNull($capturedCallback());
    }

    /**
     * Test that multiple resolve() calls use the same shared singleton (batching behavior).
     * addProductSku must be called once per SKU on the same provider instance.
     */
    public function testMultipleResolvesUseSameProviderInstance(): void
    {
        $skus = ['sku-a', 'sku-b', 'sku-c'];

        $this->productDataProvider->expects($this->exactly(3))
            ->method('addProductSku')
            ->willReturnCallback(function (string $sku) use ($skus) {
                $this->assertContains($sku, $skus);
            });

        $this->productDataProvider->expects($this->exactly(3))
            ->method('addEavAttributes')
            ->with(['name']);

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

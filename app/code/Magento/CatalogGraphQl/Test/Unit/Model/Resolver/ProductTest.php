<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\CatalogGraphQl\Model\Resolver\Product as ProductResolver;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var ProductDataProvider|MockObject
     */
    private ProductDataProvider|MockObject $productDataProvider;

    /**
     * @var ValueFactory|MockObject
     */
    private ValueFactory|MockObject $valueFactory;

    /**
     * @var ProductFieldsSelector|MockObject
     */
    private ProductFieldsSelector|MockObject $productFieldsSelector;

    /**
     * @var ProductResolver
     */
    private ProductResolver $resolver;

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
        $this->productDataProvider = $this->createMock(ProductDataProvider::class);
        $this->valueFactory = $this->createMock(ValueFactory::class);
        $this->productFieldsSelector = $this->createMock(ProductFieldsSelector::class);
        $this->field = $this->createMock(Field::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->info = $this->createMock(ResolveInfo::class);

        $this->resolver = new ProductResolver(
            $this->productDataProvider,
            $this->valueFactory,
            $this->productFieldsSelector
        );
    }

    /**
     * Test that resolve() throws GraphQlInputException when 'sku' is missing.
     */
    public function testResolveThrowsExceptionWhenSkuMissing(): void
    {
        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('No child sku found for product link.');

        $this->resolver->resolve($this->field, $this->context, $this->info, []);
    }

    /**
     * Test that resolve() queues SKU + EAV attributes on the shared singleton
     * and returns a deferred Value without triggering any DB fetch.
     */
    public function testResolveQueuesSKUOnSharedProviderAndReturnsDeferredValue(): void
    {
        $sku = 'simple-child-sku';
        $fields = ['name', 'price'];

        $this->productFieldsSelector->expects($this->once())
            ->method('getProductFieldsFromInfo')
            ->with($this->info)
            ->willReturn($fields);

        $this->productDataProvider->expects($this->once())
            ->method('addProductSku')
            ->with($sku);

        $this->productDataProvider->expects($this->once())
            ->method('addEavAttributes')
            ->with($fields);

        // Must NOT fetch during resolve()
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
     * Test that the deferred callback returns null when product data is empty.
     */
    public function testDeferredCallbackReturnsNullWhenProductNotFound(): void
    {
        $sku = 'missing-sku';

        $this->productFieldsSelector->method('getProductFieldsFromInfo')->willReturn([]);
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
     * Test that the deferred callback returns merged product data when product is found.
     */
    public function testDeferredCallbackReturnsProductData(): void
    {
        $sku = 'found-sku';

        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->willReturn(['name' => 'Test Product', 'sku' => $sku]);
        $productMock->method('getCustomAttributes')->willReturn([]);

        $this->productFieldsSelector->method('getProductFieldsFromInfo')->willReturn(['name']);
        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->method('addEavAttributes');
        $this->productDataProvider->expects($this->once())
            ->method('getProductBySku')
            ->with($sku, $this->context)
            ->willReturn(['model' => $productMock]);

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

        $result = $capturedCallback();
        $this->assertIsArray($result);
        $this->assertSame($productMock, $result['model']);
        $this->assertEquals('Test Product', $result['name']);
    }

    /**
     * Test that the deferred callback returns $value['product'] directly when it is already set,
     * bypassing getProductBySku (short-circuit path).
     */
    public function testDeferredCallbackUsesPreloadedProductData(): void
    {
        $sku = 'preloaded-sku';
        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->willReturn(['sku' => $sku]);
        $productMock->method('getCustomAttributes')->willReturn([]);

        $this->productFieldsSelector->method('getProductFieldsFromInfo')->willReturn([]);
        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->method('addEavAttributes');

        // getProductBySku must NOT be called when 'product' key is pre-populated
        $this->productDataProvider->expects($this->never())
            ->method('getProductBySku');

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
            ['sku' => $sku, 'product' => ['model' => $productMock]]
        );

        $result = $capturedCallback();
        $this->assertIsArray($result);
        $this->assertSame($productMock, $result['model']);
    }

    /**
     * Test that multiple resolve() calls all queue on the shared singleton.
     */
    public function testMultipleResolvesQueueOnSameProviderInstance(): void
    {
        $skus = ['sku-1', 'sku-2', 'sku-3'];

        $this->productFieldsSelector->method('getProductFieldsFromInfo')->willReturn([]);
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

    /**
     * Test that custom attributes are included in the returned data.
     */
    public function testDeferredCallbackMergesCustomAttributes(): void
    {
        $sku = 'custom-attr-sku';

        $customAttribute = $this->createMock(AttributeInterface::class);
        $customAttribute->method('getAttributeCode')->willReturn('custom_field');
        $customAttribute->method('getValue')->willReturn('custom_value');

        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->willReturn(['sku' => $sku]);
        $productMock->method('getCustomAttributes')->willReturn([$customAttribute]);

        $this->productFieldsSelector->method('getProductFieldsFromInfo')->willReturn([]);
        $this->productDataProvider->method('addProductSku');
        $this->productDataProvider->method('addEavAttributes');
        $this->productDataProvider->method('getProductBySku')
            ->willReturn(['model' => $productMock]);

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

        $result = $capturedCallback();
        $this->assertEquals('custom_value', $result['custom_field']);
    }
}

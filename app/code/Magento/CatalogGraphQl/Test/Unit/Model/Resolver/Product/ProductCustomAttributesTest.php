<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCustomAttributes;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\EavGraphQl\Model\Resolver\GetFilteredAttributes;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Test for ProductCustomAttributes resolver
 *
 * @see ProductCustomAttributes
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductCustomAttributesTest extends TestCase
{
    /**
     * @var ProductCustomAttributes
     */
    private ProductCustomAttributes $resolver;

    /**
     * @var GetAttributeValueInterface|MockObject
     */
    private GetAttributeValueInterface|MockObject $getAttributeValueMock;

    /**
     * @var ProductDataProvider|MockObject
     */
    private ProductDataProvider|MockObject $productDataProviderMock;

    /**
     * @var GetFilteredAttributes|MockObject
     */
    private GetFilteredAttributes|MockObject $getFilteredAttributesMock;

    /**
     * @var FilterProductCustomAttribute|MockObject
     */
    private FilterProductCustomAttribute|MockObject $filterCustomAttributeMock;

    /**
     * @var Field|MockObject
     */
    private Field|MockObject $fieldMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo|MockObject $resolveInfoMock;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $productMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $loggerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getAttributeValueMock = $this->createMock(GetAttributeValueInterface::class);
        $this->productDataProviderMock = $this->createMock(ProductDataProvider::class);
        $this->getFilteredAttributesMock = $this->createMock(GetFilteredAttributes::class);
        $this->filterCustomAttributeMock = $this->createMock(FilterProductCustomAttribute::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->productMock = $this->createMock(Product::class);

        $this->resolver = new ProductCustomAttributes(
            $this->getAttributeValueMock,
            $this->productDataProviderMock,
            $this->getFilteredAttributesMock,
            $this->filterCustomAttributeMock,
            $this->loggerMock
        );
    }

    /**
     * Test resolve with simple scalar attributes
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testResolveWithScalarAttributes(): void
    {
        $productId = 1;
        $attributeCode1 = 'description';
        $attributeCode2 = 'color';
        
        $attributeMock1 = $this->createMock(AttributeInterface::class);
        $attributeMock1->method('getAttributeCode')->willReturn($attributeCode1);
        
        $attributeMock2 = $this->createMock(AttributeInterface::class);
        $attributeMock2->method('getAttributeCode')->willReturn($attributeCode2);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->expects($this->once())
            ->method('execute')
            ->with([], ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn([
                'items' => [$attributeMock1, $attributeMock2],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->expects($this->once())
            ->method('execute')
            ->with([$attributeCode1 => 0, $attributeCode2 => 1])
            ->willReturn([$attributeCode1 => 0, $attributeCode2 => 1]);

        $this->productDataProviderMock
            ->expects($this->once())
            ->method('getProductDataById')
            ->with($productId)
            ->willReturn([
                $attributeCode1 => 'Product description',
                $attributeCode2 => 'Red'
            ]);

        $this->getAttributeValueMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnCallback(function ($entityType, $code, $value) {
                return [
                    'code' => $code,
                    'value' => $value
                ];
            });

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(2, $result['items']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals($attributeCode1, $result['items'][0]['code']);
        $this->assertEquals('Product description', $result['items'][0]['value']);
    }

    /**
     * Test resolve with flat array attribute (should be imploded)
     *
     * @return void
     */
    public function testResolveWithFlatArrayAttribute(): void
    {
        $productId = 1;
        $attributeCode = 'category_ids';
        
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->method('execute')
            ->willReturn([
                'items' => [$attributeMock],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->with($productId)
            ->willReturn([
                $attributeCode => ['2', '3', '4']
            ]);

        $this->getAttributeValueMock
            ->method('execute')
            ->willReturn([
                'code' => $attributeCode,
                'value' => '2,3,4'
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('2,3,4', $result['items'][0]['value']);
    }

    /**
     * Test resolve with multi-dimensional array attribute (JSON-encoded string)
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testResolveWithMultiDimensionalArrayAttribute(): void
    {
        $productId = 1;
        $attributeCode = 'gift_card_amounts';
        
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->method('execute')
            ->willReturn([
                'items' => [$attributeMock],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([$attributeCode => 0]);

        $multiDimensionalArray = [
            [
                'value_id' => 1,
                'website_id' => 0,
                'value' => 50.0000,
                'website_value' => 50
            ],
            [
                'value_id' => 2,
                'website_id' => 0,
                'value' => 100.0000,
                'website_value' => 100
            ]
        ];

        $expectedJson = json_encode($multiDimensionalArray);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->with($productId)
            ->willReturn([
                $attributeCode => $multiDimensionalArray
            ]);

        $this->getAttributeValueMock
            ->expects($this->once())
            ->method('execute')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode,
                $expectedJson
            )
            ->willReturn([
                'code' => $attributeCode,
                'value' => $expectedJson
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertSame($expectedJson, $result['items'][0]['value']);
        $this->assertJson($result['items'][0]['value']);
    }

    /**
     * Test resolve with filters
     *
     * @return void
     */
    public function testResolveWithFilters(): void
    {
        $productId = 1;
        $attributeCode = 'custom_attribute';
        $filters = ['is_visible_on_front' => true];
        
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->expects($this->once())
            ->method('execute')
            ->with($filters, ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn([
                'items' => [$attributeMock],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->willReturn([
                $attributeCode => 'test_value'
            ]);

        $this->getAttributeValueMock
            ->method('execute')
            ->willReturn([
                'code' => $attributeCode,
                'value' => 'test_value'
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            ['filters' => $filters]
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }

    /**
     * Test resolve skips attributes not present in product data
     *
     * @return void
     */
    public function testResolveSkipsNonExistentAttributes(): void
    {
        $productId = 1;
        $existingAttributeCode = 'description';
        $nonExistentAttributeCode = 'non_existent';
        
        $attributeMock1 = $this->createMock(AttributeInterface::class);
        $attributeMock1->method('getAttributeCode')->willReturn($existingAttributeCode);
        
        $attributeMock2 = $this->createMock(AttributeInterface::class);
        $attributeMock2->method('getAttributeCode')->willReturn($nonExistentAttributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->method('execute')
            ->willReturn([
                'items' => [$attributeMock1, $attributeMock2],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([
                $existingAttributeCode => 0,
                $nonExistentAttributeCode => 1
            ]);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->willReturn([
                $existingAttributeCode => 'Product description'
                // non_existent attribute is not in product data
            ]);

        $this->getAttributeValueMock
            ->expects($this->once()) // Only called for existing attribute
            ->method('execute')
            ->willReturn([
                'code' => $existingAttributeCode,
                'value' => 'Product description'
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']); // Only one attribute returned
    }

    /**
     * Test resolve returns errors from filtered attributes
     *
     * @return void
     */
    public function testResolveReturnsErrors(): void
    {
        $productId = 1;
        $errors = [
            [
                'type' => 'ATTRIBUTE_NOT_FOUND',
                'message' => 'Attribute not found'
            ]
        ];

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->method('execute')
            ->willReturn([
                'items' => [],
                'errors' => $errors
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([]);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->willReturn([]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals($errors, $result['errors']);
    }

    /**
     * Test resolve with empty attribute value
     *
     * @return void
     */
    public function testResolveWithEmptyAttributeValue(): void
    {
        $productId = 1;
        $attributeCode = 'empty_attribute';
        
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock
            ->method('execute')
            ->willReturn([
                'items' => [$attributeMock],
                'errors' => []
            ]);

        $this->filterCustomAttributeMock
            ->method('execute')
            ->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock
            ->method('getProductDataById')
            ->willReturn([
                $attributeCode => null // Empty value
            ]);

        $this->getAttributeValueMock
            ->method('execute')
            ->willReturn([
                'code' => $attributeCode,
                'value' => ''
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('', $result['items'][0]['value']);
    }

    /**
     * Resolve extracts scalar value from DataObject payloads.
     *
     * @return void
     */
    public function testResolveWithDataObjectAttributeValue(): void
    {
        $productId = 42;
        $attributeCode = 'wrapped_attribute';

        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $dataObject = $this->createMock(DataObject::class);
        $dataObject->expects($this->once())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn('resolved_from_data_object');

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock->method('execute')->willReturn([
            'items' => [$attributeMock],
            'errors' => []
        ]);

        $this->filterCustomAttributeMock->method('execute')->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock->method('getProductDataById')->willReturn([
            $attributeCode => $dataObject
        ]);

        $this->getAttributeValueMock
            ->expects($this->once())
            ->method('execute')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode,
                'resolved_from_data_object'
            )
            ->willReturn([
                'code' => $attributeCode,
                'value' => 'resolved_from_data_object'
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertCount(1, $result['items']);
        $this->assertSame('resolved_from_data_object', $result['items'][0]['value']);
    }

    /**
     * When DataObject yields null, attribute is omitted from items.
     *
     * @return void
     */
    public function testResolveSkipsWhenDataObjectReturnsNull(): void
    {
        $productId = 7;
        $attributeCode = 'nullable_wrapped';

        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $dataObject = $this->createMock(DataObject::class);
        $dataObject->method('getData')->with($attributeCode)->willReturn(null);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock->method('execute')->willReturn([
            'items' => [$attributeMock],
            'errors' => []
        ]);

        $this->filterCustomAttributeMock->method('execute')->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock->method('getProductDataById')->willReturn([
            $attributeCode => $dataObject
        ]);

        $this->getAttributeValueMock->expects($this->never())->method('execute');

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertSame([], $result['items']);
    }

    /**
     * Unsupported object values log a warning and are skipped.
     *
     * @return void
     */
    public function testResolveLogsWarningAndSkipsUnsupportedObjectValue(): void
    {
        $productId = 99;
        $attributeCode = 'bad_object_attr';

        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);
        $this->productMock->method('getSku')->willReturn('test-sku-99');

        $this->getFilteredAttributesMock->method('execute')->willReturn([
            'items' => [$attributeMock],
            'errors' => []
        ]);

        $this->filterCustomAttributeMock->method('execute')->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock->method('getProductDataById')->willReturn([
            $attributeCode => new stdClass()
        ]);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'Skipped product custom attribute due to unsupported value type',
                $this->callback(function (array $context) use ($attributeCode, $productId) {
                    return $context['attribute_code'] === $attributeCode
                        && $context['product_id'] === $productId
                        && $context['sku'] === 'test-sku-99'
                        && $context['value_type'] === 'stdClass';
                })
            );

        $this->getAttributeValueMock->expects($this->never())->method('execute');

        $resolver = new ProductCustomAttributes(
            $this->getAttributeValueMock,
            $this->productDataProviderMock,
            $this->getFilteredAttributesMock,
            $this->filterCustomAttributeMock,
            $loggerMock
        );

        $result = $resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertSame([], $result['items']);
    }

    /**
     * String numeric values pass through to attribute value formatter.
     *
     * @return void
     */
    public function testResolveWithIntegerAttributeValue(): void
    {
        $productId = 3;
        $attributeCode = 'numeric_attr';

        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getAttributeCode')->willReturn($attributeCode);

        $this->productMock->method('getId')->willReturn($productId);

        $this->getFilteredAttributesMock->method('execute')->willReturn([
            'items' => [$attributeMock],
            'errors' => []
        ]);

        $this->filterCustomAttributeMock->method('execute')->willReturn([$attributeCode => 0]);

        $this->productDataProviderMock->method('getProductDataById')->willReturn([
            $attributeCode => '42'
        ]);

        $this->getAttributeValueMock
            ->expects($this->once())
            ->method('execute')
            ->with(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode,
                '42'
            )
            ->willReturn([
                'code' => $attributeCode,
                'value' => '42'
            ]);

        $result = $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            []
        );

        $this->assertCount(1, $result['items']);
        $this->assertSame('42', $result['items'][0]['value']);
    }

    /**
     * Only the requested attribute codes are returned, in the requested order, without duplicates.
     *
     * @return void
     */
    public function testResolveWithAttributeCodesReturnsRequestedCodesInOrder(): void
    {
        $this->mockEligibleAttributes(['description', 'color', 'size']);
        $this->productDataProviderMock->expects($this->once())
            ->method('getProductDataById')
            ->willReturn(['description' => 'Desc', 'color' => 'Red', 'size' => 'XL']);
        $this->mockAttributeValueFormatter();

        $result = $this->resolveWithArgs(['attributeCodes' => ['size', 'description', 'size']]);

        $this->assertSame(
            [
                ['code' => 'size', 'value' => 'XL'],
                ['code' => 'description', 'value' => 'Desc']
            ],
            $result['items']
        );
        $this->assertSame([], $result['errors']);
    }

    /**
     * Requested codes that are unknown or not eligible are reported after the filter errors.
     *
     * @return void
     */
    public function testResolveWithAttributeCodesReportsUnavailableCodes(): void
    {
        $filterError = ['type' => 'FILTER_NOT_FOUND', 'message' => 'Cannot filter by "foo".'];
        $this->mockEligibleAttributes(['color'], [$filterError]);
        $this->productDataProviderMock->method('getProductDataById')->willReturn(['color' => 'Red']);
        $this->mockAttributeValueFormatter();

        $result = $this->resolveWithArgs(['attributeCodes' => ['color', 'unknown_code']]);

        $this->assertSame([['code' => 'color', 'value' => 'Red']], $result['items']);
        $this->assertSame(
            [
                $filterError,
                [
                    'type' => 'ATTRIBUTE_NOT_FOUND',
                    'message' => 'Attribute code "unknown_code" could not be found.'
                ]
            ],
            $result['errors']
        );
    }

    /**
     * Requested attributes without value are returned with an empty value, in the requested order.
     *
     * @return void
     */
    public function testResolveWithAttributeCodesReturnsRequestedAttributesWithoutValue(): void
    {
        $this->mockEligibleAttributes(['description', 'color', 'size']);
        $this->productDataProviderMock->method('getProductDataById')
            ->willReturn(['description' => 'Desc', 'color' => null]);
        $this->mockAttributeValueFormatter();

        $result = $this->resolveWithArgs(['attributeCodes' => ['size', 'color', 'description']]);

        $this->assertSame(
            [
                ['code' => 'size', 'value' => ''],
                ['code' => 'color', 'value' => ''],
                ['code' => 'description', 'value' => 'Desc']
            ],
            $result['items']
        );
    }

    /**
     * Without attributeCodes, attributes without value are still skipped.
     *
     * @return void
     */
    public function testResolveWithoutAttributeCodesSkipsAttributesWithoutValue(): void
    {
        $this->mockEligibleAttributes(['description', 'size']);
        $this->productDataProviderMock->method('getProductDataById')->willReturn(['description' => 'Desc']);
        $this->mockAttributeValueFormatter();

        $result = $this->resolveWithArgs([]);

        $this->assertSame([['code' => 'description', 'value' => 'Desc']], $result['items']);
    }

    /**
     * The product is not loaded when none of the requested codes is available.
     *
     * @return void
     */
    public function testResolveWithOnlyUnavailableAttributeCodesDoesNotLoadProduct(): void
    {
        $this->mockEligibleAttributes(['color']);
        $this->productDataProviderMock->expects($this->never())->method('getProductDataById');
        $this->getAttributeValueMock->expects($this->never())->method('execute');

        $result = $this->resolveWithArgs(['attributeCodes' => ['unknown_code']]);

        $this->assertSame([], $result['items']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame('ATTRIBUTE_NOT_FOUND', $result['errors'][0]['type']);
    }

    /**
     * An empty list of attribute codes returns all the eligible attributes, like an omitted argument.
     *
     * @return void
     */
    public function testResolveWithEmptyAttributeCodesReturnsAllEligibleAttributes(): void
    {
        $this->mockEligibleAttributes(['description', 'color']);
        $this->productDataProviderMock->method('getProductDataById')
            ->willReturn(['description' => 'Desc', 'color' => 'Red']);
        $this->mockAttributeValueFormatter();

        $result = $this->resolveWithArgs(['attributeCodes' => []]);

        $this->assertSame(
            [
                ['code' => 'description', 'value' => 'Desc'],
                ['code' => 'color', 'value' => 'Red']
            ],
            $result['items']
        );
        $this->assertSame([], $result['errors']);
    }

    /**
     * Mock the eligible attributes returned by the filters
     *
     * @param string[] $codes
     * @param array $errors
     * @return void
     */
    private function mockEligibleAttributes(array $codes, array $errors = []): void
    {
        $this->productMock->method('getId')->willReturn(1);
        $this->getFilteredAttributesMock->method('execute')->willReturn([
            'items' => array_map(
                function (string $code) {
                    $attributeMock = $this->createMock(AttributeInterface::class);
                    $attributeMock->method('getAttributeCode')->willReturn($code);
                    return $attributeMock;
                },
                $codes
            ),
            'errors' => $errors
        ]);
        $this->filterCustomAttributeMock->method('execute')->willReturnArgument(0);
    }

    /**
     * Mock the attribute value formatter to return the code and the raw value
     *
     * @return void
     */
    private function mockAttributeValueFormatter(): void
    {
        $this->getAttributeValueMock->method('execute')->willReturnCallback(
            function (string $entityType, string $code, $value) {
                return ['code' => $code, 'value' => $value];
            }
        );
    }

    /**
     * Resolve the field for the product with the given arguments
     *
     * @param array $args
     * @return array
     */
    private function resolveWithArgs(array $args): array
    {
        return $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            ['model' => $this->productMock],
            $args
        );
    }
}

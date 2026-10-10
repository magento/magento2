<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;

class ConfigurableAttributeDataTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var ConfigurableAttributeData|MockObject
     */
    protected $configurableAttributeData;

    /**
     * @var Attribute|MockObject
     */
    protected $attributeMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            ['getStoreId', 'setStoreId']
        );
        $this->attributeMock = $this->createMock(
            Attribute::class
        );
        $this->configurableAttributeData = new ConfigurableAttributeData();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareJsonAttributes()
    {
        $storeId = '1';
        $attributeId = 5;
        $attributeOptions = [
            ['value_index' => 'option_id_1', 'label' => 'label_1'],
            ['value_index' => 'option_id_2', 'label' => 'label_2'],
        ];
        $position = 2;
        $expected = [
            'attributes' => [
                $attributeId => [
                    'id' => $attributeId,
                    'code' => 'test_attribute',
                    'label' => 'Test',
                    'position' => $position,
                    'options' => [
                        0 => [
                            'id' => 'option_id_1',
                            'label' => 'label_1',
                            'products' => 'option_products_1',
                        ],
                        1 => [
                            'id' => 'option_id_2',
                            'label' => 'label_2',
                            'products' => 'option_products_2',
                        ],
                    ],
                ],
            ],
            'defaultValues' => [
                $attributeId => 'option_id_1',
            ],
        ];
        $options = [
            $attributeId => ['option_id_1' => 'option_products_1', 'option_id_2' => 'option_products_2'],
        ];

        $productAttributeMock = $this->createPartialMockWithReflection(
            EavAttribute::class,
            ['getId', 'setId', 'getAttributeCode', 'setAttributeCode', 'getStoreLabel', 'setStoreLabel']
        );
        $productAttributeMock->method('getId')->willReturn($attributeId);
        $productAttributeMock->method('getAttributeCode')->willReturn($expected['attributes'][$attributeId]['code']);
        $productAttributeMock->method('getStoreLabel')->willReturn($expected['attributes'][$attributeId]['label']);

        $attributeMock = $this->createPartialMock(ConfigurableAttribute::class, []);
        $attributeMock->setProductAttribute($productAttributeMock);
        $attributeMock->setPosition($position);
        $attributeMock->setAttributeId($attributeId);
        $attributeMock->setOptions($attributeOptions);

        $this->product->method('getStoreId')->willReturn($storeId);

        $configurableProduct = $this->createMock(
            Configurable::class
        );
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn([$attributeMock]);

        $configuredValueMock = $this->createMock(DataObject::class);
        $configuredValueMock->method('getData')->willReturn($expected['defaultValues'][$attributeId]);

        // Configure product mock with expected values
        $this->product->setTypeInstance($configurableProduct);
        $this->product->setHasPreconfiguredValues(true);
        $this->product->setPreconfiguredValues($configuredValueMock);

        $this->assertEquals($expected, $this->configurableAttributeData->getAttributesData($this->product, $options));
    }

    /**
     * Options that are not used by any enabled variation must be excluded, while values used by enabled
     * but out-of-stock variations must be kept so they are still rendered as unavailable.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDisabledVariationOptionsAreExcluded()
    {
        $storeId = '1';
        $attributeId = 5;
        $attributeOptions = [
            ['value_index' => 'in_stock', 'label' => 'In Stock'],
            ['value_index' => 'out_of_stock', 'label' => 'Out Of Stock'],
            ['value_index' => 'disabled', 'label' => 'Disabled'],
        ];
        $position = 2;

        // Only "in_stock" has salable products; "out_of_stock" appears in the index (enabled product,
        // no salable products); "disabled" appears nowhere (only a disabled simple product uses it).
        $options = [
            $attributeId => ['in_stock' => ['1']],
            'index' => [
                '1' => [$attributeId => 'in_stock'],
                '2' => [$attributeId => 'out_of_stock'],
            ],
        ];

        $expected = [
            'attributes' => [
                $attributeId => [
                    'id' => $attributeId,
                    'code' => 'test_attribute',
                    'label' => 'Test',
                    'position' => $position,
                    'options' => [
                        0 => [
                            'id' => 'in_stock',
                            'label' => 'In Stock',
                            'products' => ['1'],
                        ],
                        1 => [
                            'id' => 'out_of_stock',
                            'label' => 'Out Of Stock',
                            'products' => [],
                        ],
                    ],
                ],
            ],
            'defaultValues' => [
                $attributeId => null,
            ],
        ];

        $productAttributeMock = $this->createPartialMockWithReflection(
            EavAttribute::class,
            ['getId', 'setId', 'getAttributeCode', 'setAttributeCode', 'getStoreLabel', 'setStoreLabel']
        );
        $productAttributeMock->method('getId')->willReturn($attributeId);
        $productAttributeMock->method('getAttributeCode')->willReturn($expected['attributes'][$attributeId]['code']);
        $productAttributeMock->method('getStoreLabel')->willReturn($expected['attributes'][$attributeId]['label']);

        $attributeMock = $this->createPartialMock(ConfigurableAttribute::class, []);
        $attributeMock->setProductAttribute($productAttributeMock);
        $attributeMock->setPosition($position);
        $attributeMock->setAttributeId($attributeId);
        $attributeMock->setOptions($attributeOptions);

        $this->product->method('getStoreId')->willReturn($storeId);

        $configurableProduct = $this->createMock(Configurable::class);
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn([$attributeMock]);

        $this->product->setTypeInstance($configurableProduct);
        $this->product->setHasPreconfiguredValues(false);

        $this->assertEquals($expected, $this->configurableAttributeData->getAttributesData($this->product, $options));
    }
}

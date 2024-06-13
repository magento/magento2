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

class ConfigurableAttributeDataTest extends TestCase
{
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
        $this->product = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
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

        $productAttributeMock = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();
        $productAttributeMock->setId($attributeId);
        $productAttributeMock->setAttributeCode($expected['attributes'][$attributeId]['code']);

        $attributeMock = $this->createPartialMock(ConfigurableAttribute::class, []);
        $attributeMock->setProductAttribute($productAttributeMock);
        $attributeMock->setPosition($position);
        $attributeMock->setAttributeId($attributeId);
        $attributeMock->setOptions($attributeOptions);

        $this->product->setStoreId($storeId);
        $productAttributeMock->setStoreLabel($expected['attributes'][$attributeId]['label']);

        $configurableProduct = $this->createMock(
            Configurable::class
        );
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn([$attributeMock]);

        $configuredValueMock = $this->createMock(DataObject::class);
        $configuredValueMock->method('getData')->willReturn($expected['defaultValues'][$attributeId]);

        // Configure ProductTestHelper with expected values
        $this->product->setTypeInstance($configurableProduct);
        $this->product->setHasPreconfiguredValues(true);
        $this->product->setPreconfiguredValues($configuredValueMock);

        $this->assertEquals($expected, $this->configurableAttributeData->getAttributesData($this->product, $options));
    }
}

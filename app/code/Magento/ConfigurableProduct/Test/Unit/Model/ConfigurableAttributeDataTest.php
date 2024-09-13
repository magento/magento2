<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
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
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods(['setParentId', 'hasPreconfiguredValues'])
            ->onlyMethods(['getTypeInstance', 'getPreconfiguredValues', 'getPriceInfo', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $productAttributeMock = $this->getMockBuilder(\Magento\Catalog\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAttributeLabel'])
            ->onlyMethods(['getStoreLabel', 'getAttributeCode', 'getId'])
            ->getMock();
        $productAttributeMock->expects($this->once())
            ->method('getId')
            ->willReturn($attributeId);
        $productAttributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($expected['attributes'][$attributeId]['code']);

        $attributeMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['getProductAttribute'])
            ->onlyMethods(['getLabel', 'getOptions', 'getAttributeId', 'getPosition'])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getProductAttribute')
            ->willReturn($productAttributeMock);
        $attributeMock->expects($this->once())
            ->method('getPosition')
            ->willReturn($position);

        $this->product->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productAttributeMock->expects($this->once())
            ->method('getStoreLabel')
            ->with($storeId)
            ->willReturn($expected['attributes'][$attributeId]['label']);

        $attributeMock->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeId);
        $attributeMock->expects($this->atLeastOnce())
            ->method('getOptions')
            ->willReturn($attributeOptions);

        $configurableProduct = $this->getMockBuilder(
            Configurable::class
        )->disableOriginalConstructor()
            ->getMock();
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn([$attributeMock]);

        $configuredValueMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configuredValueMock->expects($this->any())
            ->method('getData')
            ->willReturn($expected['defaultValues'][$attributeId]);

        $this->product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($configurableProduct);
        $this->product->expects($this->once())
            ->method('hasPreconfiguredValues')
            ->willReturn(true);
        $this->product->expects($this->once())
            ->method('getPreconfiguredValues')
            ->willReturn($configuredValueMock);

        $this->assertEquals($expected, $this->configurableAttributeData->getAttributesData($this->product, $options));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

/**
 * Class CustomOptionTest
 */
class ConfigurableAttributeDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurableAttributeData;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute|
     * \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, [
                'getTypeInstance',
                'setParentId',
                'hasPreconfiguredValues',
                'getPreconfiguredValues',
                'getPriceInfo',
                'getStoreId'
            ]);
        $this->attributeMock = $this->createMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class
        );
        $this->configurableAttributeData = new \Magento\ConfigurableProduct\Model\ConfigurableAttributeData();
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
            ->setMethods(['getStoreLabel', '__wakeup', 'getAttributeCode', 'getId', 'getAttributeLabel'])
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
            ->setMethods(['getProductAttribute', '__wakeup', 'getLabel', 'getOptions', 'getAttributeId', 'getPosition'])
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
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        )->disableOriginalConstructor()->getMock();
        $configurableProduct->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->product)
            ->willReturn([$attributeMock]);

        $configuredValueMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
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

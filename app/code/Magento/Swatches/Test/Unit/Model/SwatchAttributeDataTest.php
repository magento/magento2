<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Model;

/**
 * Class SwatchAttributeDataTest
 */
class SwatchAttributeDataTest extends \PHPUnit_Framework_TestCase
{

    public function testGetAttributesData()
    {
        $attributeOption = [
            'value_index' => '1',
            'label' => 'attribute_option_label',
            'products' => []
        ];
        $attributeData = [
            'id' => '1',
            'code' => 'attribute_code',
            'label' => 'attribute_label',
            'options' => [
                [
                    'id' => $attributeOption['value_index'],
                    'label' => $attributeOption['label'],
                    'products' => $attributeOption['products']
                ]
            ]
        ];
        $storeId = '1';
        $options = [$attributeData['id'], $attributeOption['value_index']];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Swatches\Model\SwatchAttributeData $swatchesAttributeData */
        $swatchesAttributeData = $objectManager->getObject('Magento\Swatches\Model\SwatchAttributeData');
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'getStoreId', 'hasPreconfiguredValues'])
            ->disableOriginalConstructor()
            ->getMock();
        $productType = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);
        $configurableAttribute = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute'
        )
            ->setMethods(['getOptions', 'getAttributeId', 'getProductAttribute'])
            ->disableOriginalConstructor()
            ->getMock();
        $productType->expects($this->once())->method('getConfigurableAttributes')->with($product)
            ->willReturn([$configurableAttribute]);
        $configurableAttribute->expects($this->once())->method('getOptions')->willReturn([$attributeOption]);
        $configurableAttribute->expects($this->any())->method('getAttributeId')->willReturn($attributeData['id']);
        $productAttribute = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $configurableAttribute->expects($this->once())->method('getProductAttribute')->willReturn($productAttribute);
        $productAttribute->expects($this->once())->method('getId')->willReturn($attributeData['id']);
        $productAttribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeData['code']);
        $product->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $productAttribute->expects($this->once())->method('getStoreLabel')->with($storeId)
            ->willReturn($attributeData['label']);
        $product->expects($this->once())->method('hasPreconfiguredValues')->willReturn(false);
        $this->assertEquals(
            [
                'attributes' => [$attributeData['id'] => $attributeData],
                'defaultValues' => [$attributeData['id'] => null]
            ],
            $swatchesAttributeData->getAttributesData($product, $options)
        );


    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

class ConfigurableProductManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productVariationBuilder;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $option;

    protected function setUp()
    {
        $this->attributeRepository = $this->getMock('\Magento\Catalog\Api\ProductAttributeRepositoryInterface');
        $this->product = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->option = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            [],
            [],
            '',
            false
        );
        $this->productVariationBuilder = $this->getMock(
            '\Magento\ConfigurableProduct\Model\ProductVariationsBuilder',
            [],
            [],
            '',
            false
        );

        $this->model = new ConfigurableProductManagement($this->attributeRepository, $this->productVariationBuilder);
    }

    public function testGenerateVariation()
    {
        $data = ['someKey' => 'someValue'];
        $attributeOption = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Option', [], [], '', false);
        $attributeOption->expects($this->once())->method('getData')->willReturn(['key' => 'value']);

        $attribute = $this->getMock('\Magento\Catalog\Model\Resource\Eav\Attribute', [], [], '', false);
        $attribute->expects($this->any())->method('getOptions')->willReturn([$attributeOption]);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn(10);

        $this->option->expects($this->any())->method('getAttributeId')->willReturn(1);
        $this->attributeRepository->expects($this->once())->method('get')->with(1)->willReturn($attribute);

        $this->option->expects($this->any())->method('getData')->willReturn($data);

        $expectedAttributes = [
            1 => [
                'someKey' => 'someValue',
                'options' => [['key' => 'value']],
                'attribute_code' => 10,
            ]
        ];

        $this->productVariationBuilder->expects($this->once())
            ->method('create')
            ->with($this->product, $expectedAttributes)
            ->willReturn(['someObject']);

        $expected = ['someObject'];
        $this->assertEquals($expected, $this->model->generateVariation($this->product, [$this->option]));
    }
}

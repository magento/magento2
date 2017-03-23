<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\ConfigurableProductManagement;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;

class ConfigurableProductManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductManagement
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\ProductVariationsBuilder|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productsFactoryMock;

    protected function setUp()
    {
        $this->attributeRepository = $this->getMock(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
        $this->product = $this->getMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->option = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class,
            [],
            [],
            '',
            false
        );
        $this->productVariationBuilder = $this->getMock(
            \Magento\ConfigurableProduct\Model\ProductVariationsBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->productsFactoryMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->model = new ConfigurableProductManagement(
            $this->attributeRepository,
            $this->productVariationBuilder,
            $this->productsFactoryMock
        );
    }

    public function testGenerateVariation()
    {
        $data = ['someKey' => 'someValue'];
        $attributeOption = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Option::class, [], [], '', false);
        $attributeOption->expects($this->once())->method('getData')->willReturn(['key' => 'value']);

        $attribute = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
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

    public function testGetEnabledCount()
    {
        $statusEnabled = 1;
        $productsMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->productsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($productsMock);
        $productsMock
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('status', $statusEnabled)
            ->willReturnSelf();
        $productsMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount($statusEnabled)
        );
    }

    public function testGetDisabledCount()
    {
        $statusDisabled = 2;
        $productsMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->productsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($productsMock);
        $productsMock
            ->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('status', $statusDisabled)
            ->willReturnSelf();
        $productsMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount($statusDisabled)
        );
    }
}

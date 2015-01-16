<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;


class ProductVariationsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductVariationsBuilder
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customAttributeBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $variationMatrix;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected function setUp()
    {
        $this->customAttributeBuilder = $this->getMock(
            '\Magento\Framework\Api\AttributeDataBuilder',
            [],
            [],
            '',
            false
        );

        $this->product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getData', 'getPrice', 'getName', 'getSku', '__wakeup', 'getCustomAttributes'],
            [],
            '',
            false
        );

        $this->productFactory = $this->getMock('\Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);

        $this->variationMatrix = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix',
            [],
            [],
            '',
            false
        );

        $this->model = new ProductVariationsBuilder(
            $this->productFactory,
            $this->customAttributeBuilder,
            $this->variationMatrix
        );
    }

    public function testCreate()
    {
        $output = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['setPrice', '__wakeup', 'setData', 'getCustomAttributes', 'setName', 'setSku', 'setVisibility'],
            [],
            '',
            false
        );
        $attributes = [10 => ['attribute_code' => 'sort_order']];
        $variations = [
            [10 => ['value' => 15, 'price' => ['pricing_value' => 10]]],
        ];
        $this->variationMatrix->expects($this->once())
            ->method('getVariations')
            ->with($attributes)
            ->willReturn($variations);

        $this->productFactory->expects($this->once())->method('create')->willReturn($output);
        $productData = ['id' => '10', 'title' => 'simple'];
        $this->product->expects($this->once())->method('getData')->willReturn($productData);
        $this->product->expects($this->once())->method('getName')->willReturn('simple');
        $this->product->expects($this->once())->method('getSku')->willReturn('simple-sku');

        $output->expects($this->at(0))->method('setData')->with($productData);

        $attribute = $this->getMock('\Magento\Framework\Api\AttributeInterface');
        $this->customAttributeBuilder->expects($this->once())
            ->method('setAttributeCode')
            ->with('sort_order')
            ->willReturnSelf();

        $this->customAttributeBuilder->expects($this->once())
            ->method('setValue')
            ->with(15)
            ->willReturnSelf();

        $this->customAttributeBuilder->expects($this->once())
            ->method('create')
            ->willReturn($attribute);

        $output->expects($this->once())->method('getCustomAttributes')->willReturn([]);

        $output->expects($this->at(2))->method('setData')->with('custom_attributes', ['sort_order' => $attribute]);
        $output->expects($this->once())->method('setPrice')->with(10);
        $output->expects($this->once())->method('setName')->with('simple-15');
        $output->expects($this->once())->method('setSku')->with('simple-sku-15');
        $output->expects($this->once())->method('setVisibility')
            ->with(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);

        $this->assertEquals([$output], $this->model->create($this->product, $attributes));
    }
}

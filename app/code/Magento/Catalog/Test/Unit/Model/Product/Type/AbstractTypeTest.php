<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AbstractTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productResource;

    /**
     * @var \Magento\Catalog\Model\Entity\Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(\Magento\Catalog\Model\Product\Type\Simple::class);

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getHasOptions', '__wakeup', '__sleep', 'getResource', 'getStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productResource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product::class)
            ->setMethods(['getSortedAttributes', 'loadAllAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->product->expects($this->any())->method('getResource')->willReturn($this->productResource);

        $this->attribute = $this->getMockBuilder(\Magento\Catalog\Model\Entity\Attribute::class)
            ->setMethods(['getGroupSortPath', 'getSortPath', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIsSalable()
    {
        $this->product->expects($this->any())->method('getStatus')->willReturn(
            Status::STATUS_ENABLED
        );
        $this->product->setData('is_salable', 3);
        $this->assertTrue($this->model->isSalable($this->product));
    }

    public function testGetAttributeById()
    {
        $this->productResource->expects($this->any())->method('loadAllAttributes')->willReturn(
            $this->productResource
        );
        $this->productResource->expects($this->any())->method('getSortedAttributes')->willReturn(
            [$this->attribute]
        );
        $this->attribute->setId(1);

        $this->assertEquals($this->attribute, $this->model->getAttributeById(1, $this->product));
        $this->assertNull($this->model->getAttributeById(0, $this->product));
    }

    /**
     * @dataProvider attributeCompareProvider
     */
    public function testAttributesCompare($attr1, $attr2, $expectedResult)
    {
        $attribute = $this->attribute;
        $attribute->expects($this->any())->method('getSortPath')->willReturn(1);

        $attribute2 = clone $attribute;

        $attribute->expects($this->any())->method('getGroupSortPath')->willReturn($attr1);
        $attribute2->expects($this->any())->method('getGroupSortPath')->willReturn($attr2);

        $this->assertEquals($expectedResult, $this->model->attributesCompare($attribute, $attribute2));
    }

    /**
     * @return array
     */
    public function attributeCompareProvider()
    {
        return [
            [2, 2, 0],
            [2, 1, 1],
            [1, 2, -1]
        ];
    }

    public function testGetSetAttributes()
    {
        $this->productResource->expects($this->once())->method('loadAllAttributes')->willReturn(
            $this->productResource
        );
        $this->productResource->expects($this->once())->method('getSortedAttributes')->willReturn(5);
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
        //Call the method for a second time, the cached copy should be used
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
    }

    public function testHasOptions()
    {
        $this->product->expects($this->once())->method('getHasOptions')->willReturn(true);
        $this->assertTrue($this->model->hasOptions($this->product));
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AbstractTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productResource;

    /**
     * @var \Magento\Catalog\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject('Magento\Catalog\Model\Product\Type\Simple');

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getHasOptions', '__wakeup', '__sleep', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productResource = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product')
            ->setMethods(['getSortedAttributes', 'loadAllAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->product->expects($this->any())->method('getResource')->will($this->returnValue($this->productResource));

        $this->attribute = $this->getMockBuilder('Magento\Catalog\Model\Entity\Attribute')
            ->setMethods(['getGroupSortPath', 'getSortPath', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIsSalable()
    {
        $this->product->expects($this->any())->method('getStatus')->will(
            $this->returnValue(Status::STATUS_ENABLED)
        );
        $this->product->setData('is_salable', 3);
        $this->assertEquals(true, $this->model->isSalable($this->product));
    }

    public function testGetAttributeById()
    {
        $this->productResource->expects($this->any())->method('loadAllAttributes')->will(
            $this->returnValue($this->productResource)
        );
        $this->productResource->expects($this->any())->method('getSortedAttributes')->will(
            $this->returnValue([$this->attribute])
        );
        $this->attribute->setId(1);

        $this->assertEquals($this->attribute, $this->model->getAttributeById(1, $this->product));
        $this->assertNull($this->model->getAttributeById(0, $this->product));
    }

    /**
     * @deprecated
     */
    public function testGetEditableAttributes()
    {
        $expected = [$this->attribute];
        $this->productResource->expects($this->any())->method('loadAllAttributes')->will(
            $this->returnValue($this->productResource)
        );
        $this->productResource->expects($this->any())->method('getSortedAttributes')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->model->getEditableAttributes($this->product));
    }

    /**
     * @dataProvider attributeCompareProvider
     */
    public function testAttributesCompare($attr1, $attr2, $expectedResult)
    {
        $attribute = $this->attribute;
        $attribute->expects($this->any())->method('getSortPath')->will($this->returnValue(1));

        $attribute2 = clone $attribute;

        $attribute->expects($this->any())->method('getGroupSortPath')->will($this->returnValue($attr1));
        $attribute2->expects($this->any())->method('getGroupSortPath')->will($this->returnValue($attr2));

        $this->assertEquals($expectedResult, $this->model->attributesCompare($attribute, $attribute2));
    }

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
        $this->productResource->expects($this->once())->method('loadAllAttributes')->will(
            $this->returnValue($this->productResource)
        );
        $this->productResource->expects($this->once())->method('getSortedAttributes')->will($this->returnValue(5));
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
        //Call the method for a second time, the cached copy should be used
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
    }

    public function testHasOptions()
    {
        $this->product->expects($this->once())->method('getHasOptions')->will($this->returnValue(true));
        $this->assertEquals(true, $this->model->hasOptions($this->product));
    }
}

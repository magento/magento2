<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin
     */
    private $model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $magentoObject;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->registry = $this->getMockBuilder('Magento\Framework\Registry')
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeFactory = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute'
        )
            ->setMethods(['getUsedAttributes', 'getAttributeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->magentoObject = $this->getMockBuilder('Magento\Framework\DataObject')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            'Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin',
            ['registry' => $this->registry, 'attributeFactory' => $this->attributeFactory]
        );
    }

    public function testAroundMap()
    {
        $attrSetId = 333;
        $expected = ['is_configurable' => 1];

        /** @var \PHPUnit_Framework_MockObject_MockObject $attributeMapper */
        $attributeMapper = $this->getMockBuilder(
            'Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject $attribute */
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $proceed = function (Attribute $attribute) {
            return [];
        };

        $this->attributeFactory->expects($this->once())->method('create')
            ->will($this->returnValue($this->attribute));

        $this->attribute->expects($this->once())->method('getUsedAttributes')
            ->with($this->equalTo($attrSetId))
            ->will($this->returnValue([$attrSetId]));

        $attribute->expects($this->once())->method('getAttributeId')
            ->will($this->returnValue($attrSetId));

        $this->registry->expects($this->once())->method('registry')
            ->with($this->equalTo('current_attribute_set'))
            ->will($this->returnValue($this->magentoObject));

        $this->magentoObject->expects($this->once())->method('getId')->will($this->returnValue($attrSetId));

        $result = $this->model->aroundMap($attributeMapper, $proceed, $attribute);
        $this->assertEquals($expected, $result);
    }
}

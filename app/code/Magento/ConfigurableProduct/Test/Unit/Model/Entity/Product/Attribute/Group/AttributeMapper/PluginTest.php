<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;
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
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    private $magentoObject;

    /**
     * @var AttributeMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeFactory = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->magentoObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeMapper = $this->getMockBuilder(AttributeMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->setMethods(['getUsedAttributes', 'getAttributeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            \Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin::class,
            [
                'registry' => $this->registry,
                'attributeFactory' => $this->attributeFactory,
                'setId' => '10',
            ]
        );
    }

    public function testBeforeMap()
    {
        $this->registry->expects(static::once())->method('registry')
            ->with('current_attribute_set')
            ->willReturn($this->magentoObject);
        $this->magentoObject->expects(static::once())->method('getId')->willReturn('10');

        $this->model->beforeMap($this->attributeMapper);
    }

    public function testAfterMap()
    {
        $attrSetId = '10';
        $expected = ['is_configurable' => 1];

        $this->attributeFactory->expects(static::once())->method('create')
            ->willReturn($this->attribute);

        $this->attribute->expects(static::once())->method('getUsedAttributes')
            ->with($attrSetId)
            ->willReturn([$attrSetId]);

        $this->attribute->expects(static::once())->method('getAttributeId')
            ->willReturn($attrSetId);

        $result = $this->model->afterMap($this->attributeMapper, [], $this->attribute);
        $this->assertEquals($expected, $result);
    }
}

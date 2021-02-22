<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin
     */
    private $model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $magentoObject;

    protected function setUp(): void
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

        $this->attribute = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class
        )
            ->setMethods(['getUsedAttributes', 'getAttributeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->magentoObject = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            \Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin::class,
            ['registry' => $this->registry, 'attributeFactory' => $this->attributeFactory]
        );
    }

    public function testAroundMap()
    {
        $attrSetId = 333;
        $expected = ['is_configurable' => 1];

        /** @var \PHPUnit\Framework\MockObject\MockObject $attributeMapper */
        $attributeMapper = $this->getMockBuilder(
            \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit\Framework\MockObject\MockObject $attribute */
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $proceed = function (Attribute $attribute) {
            return [];
        };

        $this->attributeFactory->expects($this->once())->method('create')
            ->willReturn($this->attribute);

        $this->attribute->expects($this->once())->method('getUsedAttributes')
            ->with($this->equalTo($attrSetId))
            ->willReturn([$attrSetId]);

        $attribute->expects($this->once())->method('getAttributeId')
            ->willReturn($attrSetId);

        $this->registry->expects($this->once())->method('registry')
            ->with($this->equalTo('current_attribute_set'))
            ->willReturn($this->magentoObject);

        $this->magentoObject->expects($this->once())->method('getId')->willReturn($attrSetId);

        $result = $this->model->aroundMap($attributeMapper, $proceed, $attribute);
        $this->assertEquals($expected, $result);
    }
}

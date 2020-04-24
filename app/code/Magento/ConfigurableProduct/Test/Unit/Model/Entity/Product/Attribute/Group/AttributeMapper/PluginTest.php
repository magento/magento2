<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;
use Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    private $model;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var MockObject
     */
    private $attributeFactory;

    /**
     * @var MockObject
     */
    private $attribute;

    /**
     * @var DataObject|MockObject
     */
    private $magentoObject;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->registry = $this->getMockBuilder(Registry::class)
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeFactory = $this->getMockBuilder(
            AttributeFactory::class
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

        $this->magentoObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $helper->getObject(
            Plugin::class,
            ['registry' => $this->registry, 'attributeFactory' => $this->attributeFactory]
        );
    }

    public function testAroundMap()
    {
        $attrSetId = 333;
        $expected = ['is_configurable' => 1];

        /** @var MockObject $attributeMapper */
        $attributeMapper = $this->getMockBuilder(
            AttributeMapperInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Attribute|MockObject $attribute */
        $attribute = $this->getMockBuilder(Attribute::class)
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

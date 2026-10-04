<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;
use Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class PluginTest extends TestCase
{
    use MockCreationTrait;

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

        $this->registry = $this->createPartialMock(Registry::class, ['registry']);

        $this->attributeFactory = $this->createPartialMock(
            AttributeFactory::class,
            ['create']
        );

        $this->attribute = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute::class,
            ['getUsedAttributes']
        );

        $this->magentoObject = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getId', 'setId']
        );
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
        $attributeMapper = $this->createMock(
            AttributeMapperInterface::class
        );

        /** @var Attribute|MockObject $attribute */
        $attribute = $this->createMock(Attribute::class);

        $proceed = function (Attribute $attribute) {
            return [];
        };

        $this->attributeFactory->expects($this->once())->method('create')
            ->willReturn($this->attribute);

        $this->attribute->expects($this->once())->method('getUsedAttributes')
            ->with($attrSetId)
            ->willReturn([$attrSetId]);

        $attribute->expects($this->once())->method('getAttributeId')
            ->willReturn($attrSetId);

        $this->registry->expects($this->once())->method('registry')
            ->with('current_attribute_set')
            ->willReturn($this->magentoObject);

        $this->magentoObject->method('getId')->willReturn($attrSetId);

        $result = $this->model->aroundMap($attributeMapper, $proceed, $attribute);
        $this->assertEquals($expected, $result);
    }
}

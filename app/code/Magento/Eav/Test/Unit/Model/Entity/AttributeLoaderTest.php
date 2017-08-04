<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\AttributeLoader;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;

class AttributeLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityTypeMock;

    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->entityMock = $this->getMockBuilder(AbstractEntity::class)->disableOriginalConstructor()->getMock();
        $this->entityTypeMock = $this->getMockBuilder(Type::class)->disableOriginalConstructor()->getMock();
        $this->attributeLoader = new AttributeLoader(
            $this->configMock,
            $this->objectManagerMock
        );
    }

    /**
     * Test for loadAllAttributes method.
     *
     * @return void
     */
    public function testLoadAllAttributes()
    {
        $attributeCode = 'bar';
        $entityTypeId = 1;
        $dataObject = new DataObject();
        $this->entityMock->expects($this->atLeastOnce())->method('getEntityType')->willReturn($this->entityTypeMock);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([$attributeCode]);
        $this->entityTypeMock->expects($this->atLeastOnce())->method('getId')->willReturn($entityTypeId);
        $this->configMock->expects($this->once())->method('getEntityAttributes')->willReturn([]);
        $this->entityMock->expects($this->once())->method('unsetAttributes')->willReturnSelf();
        $this->entityTypeMock->expects($this->once())
            ->method('getAttributeModel')->willReturn(\Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL);
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->setMethods(['setAttributeCode', 'setBackendType', 'setIsGlobal', 'setEntityType', 'setEntityTypeId'])
            ->disableOriginalConstructor()->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')->with(\Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL)->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($attributeCode)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendType')
            ->with(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::TYPE_STATIC)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsGlobal')->with(1)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setEntityType')->with($this->entityTypeMock)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setEntityTypeId')->with($entityTypeId)->willReturnSelf();
        $this->entityMock->expects($this->once())->method('addAttribute')->with($attributeMock)->willReturnSelf();
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }

    /**
     * Test for loadAllAttributes method with attribute codes present in default attributes.
     *
     * @return void
     */
    public function testLoadAllAttributesAttributeCodesPresentInDefaultAttributes()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)
            ->disableOriginalConstructor()->getMock();
        $attributeCodes = ['bar' => $attributeMock];
        $defaultAttributes = ['bar'];
        $dataObject = new DataObject();
        $this->entityMock->expects($this->once())->method('getEntityType')->willReturn($this->entityTypeMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributes')->willReturn($attributeCodes);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn($defaultAttributes);
        $this->entityMock->expects($this->once())->method('unsetAttributes')->willReturnSelf();
        $this->entityMock->expects($this->atLeastOnce())
            ->method('addAttribute')->with($attributeMock)->willReturnSelf();
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }
}

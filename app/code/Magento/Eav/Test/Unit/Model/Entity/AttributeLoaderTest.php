<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
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

    protected function setUp()
    {
        $this->configMock = $this->getMock(Config::class, [], [], '', false);
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $this->entityMock = $this->getMock(AbstractEntity::class, [], [], '', false);
        $this->entityTypeMock = $this->getMock(Type::class, [], [], '', false);
        $this->attributeLoader = new AttributeLoader(
            $this->configMock,
            $this->objectManagerMock
        );
    }

    public function testLoadAllAttributes()
    {
        $attributeCodes = ['foo'];
        $defaultAttributes = ['bar'];
        $attributeModel = Attribute::class;
        $entityTypeId = 1;
        $dataObject = new DataObject();
        $this->entityMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->willReturn($attributeCodes, $dataObject);
        $this->entityMock->expects($this->once())
            ->method('getDefaultAttributes')
            ->willReturn($defaultAttributes);
        $this->entityTypeMock->expects($this->any())
            ->method('getId')
            ->willReturn($entityTypeId);
        $this->entityTypeMock->expects($this->once())
            ->method('getAttributeModel')
            ->willReturn($attributeModel);
        $attributeMock = $this->getMock(
            \Magento\Eav\Model\Attribute::class,
            [
                'setAttributeCode',
                'setBackendType',
                'setIsGlobal',
                'setEntityType',
                'setEntityTypeId'
            ],
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($attributeModel)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('setAttributeCode')
            ->with($defaultAttributes[0])
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setBackendType')
            ->with(AbstractAttribute::TYPE_STATIC)
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setIsGlobal')
            ->with(1)
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setEntityType')
            ->with($this->entityTypeMock)
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setEntityTypeId')
            ->with($entityTypeId)
            ->willReturnSelf();
        $this->entityMock->expects($this->once())
            ->method('addAttribute')
            ->with($attributeMock);
        $this->entityMock->expects($this->once())
            ->method('getAttribute')
            ->with($attributeCodes[0]);
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }

    public function testLoadAllAttributesAttributeCodesPresentInDefaultAttributes()
    {
        $attributeCodes = ['bar'];
        $defaultAttributes = ['bar'];
        $dataObject = new DataObject();
        $this->entityMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributeCodes')
            ->willReturn($attributeCodes, $dataObject);
        $this->entityMock->expects($this->once())
            ->method('getDefaultAttributes')
            ->willReturn($defaultAttributes);
        $this->objectManagerMock->expects($this->never())
            ->method('create');
        $this->entityMock->expects($this->once())
            ->method('getAttribute')
            ->with($attributeCodes[0]);
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }
}

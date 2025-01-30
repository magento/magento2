<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\AttributeLoader;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeLoaderTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var AbstractEntity|MockObject
     */
    private $entityMock;

    /**
     * @var Type|MockObject
     */
    private $entityTypeMock;

    /**
     * @var AttributeLoader
     */
    private $attributeLoader;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->entityMock = $this->createMock(AbstractEntity::class);
        $this->entityTypeMock = $this->createMock(Type::class);
        $this->attributeLoader = new AttributeLoader(
            $this->configMock,
            $this->objectManagerMock
        );
    }

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
            ->method('getAttributeModel')->willReturn(Entity::DEFAULT_ATTRIBUTE_MODEL);
        $attributeMock = $this->getMockBuilder(EntityAttribute::class)
            ->addMethods(['setIsGlobal'])
            ->onlyMethods(['setAttributeCode', 'setBackendType', 'setEntityType', 'setEntityTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')->with(Entity::DEFAULT_ATTRIBUTE_MODEL)->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('setAttributeCode')->with($attributeCode)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setBackendType')
            ->with(AbstractAttribute::TYPE_STATIC)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setIsGlobal')->with(1)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setEntityType')->with($this->entityTypeMock)->willReturnSelf();
        $attributeMock->expects($this->once())->method('setEntityTypeId')->with($entityTypeId)->willReturnSelf();
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }

    public function testLoadAllAttributesAttributeCodesPresentInDefaultAttributes()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Attribute::class)->addMethods(['setIsGlobal'])
            ->onlyMethods(['setAttributeCode', 'setBackendType', 'setEntityType', 'setEntityTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeCodes = ['bar' => $attributeMock];
        $defaultAttributes = ['bar'];
        $dataObject = new DataObject();
        $this->entityMock->expects($this->once())->method('getEntityType')->willReturn($this->entityTypeMock);
        $this->configMock->expects($this->once())
            ->method('getEntityAttributes')->willReturn($attributeCodes);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn($defaultAttributes);
        $this->entityMock->expects($this->once())->method('unsetAttributes')->willReturnSelf();
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->attributeLoader->loadAllAttributes($this->entityMock, $dataObject);
    }
}

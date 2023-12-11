<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ConfigFactory;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeManagementTest extends TestCase
{
    /**
     * @var AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    private $setRepositoryMock;

    /**
     * @var Collection|MockObject
     */
    private $attributeCollectionMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var ConfigFactory|MockObject
     */
    private $entityTypeFactoryMock;

    /**
     * @var AttributeGroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var AttributeRepositoryInterface|MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var Attribute|MockObject
     */
    private $attributeResourceMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $attributeCollectionFactoryMock;

    protected function setUp(): void
    {
        $this->setRepositoryMock =
            $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        $this->attributeCollectionMock =
            $this->createMock(Collection::class);
        $this->eavConfigMock =
            $this->createMock(Config::class);
        $this->entityTypeFactoryMock =
            $this->createPartialMock(ConfigFactory::class, ['create']);
        $this->groupRepositoryMock =
            $this->getMockForAbstractClass(AttributeGroupRepositoryInterface::class);
        $this->attributeRepositoryMock =
            $this->getMockForAbstractClass(AttributeRepositoryInterface::class);
        $this->attributeResourceMock =
            $this->createMock(Attribute::class);
        $this->attributeCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeManagement = new AttributeManagement(
            $this->setRepositoryMock,
            $this->attributeCollectionMock,
            $this->eavConfigMock,
            $this->entityTypeFactoryMock,
            $this->groupRepositoryMock,
            $this->attributeRepositoryMock,
            $this->attributeResourceMock,
            $this->attributeCollectionFactoryMock
        );
    }

    public function testAssignNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'The AttributeSet with a "2" ID doesn\'t exist. Verify the attributeSet and try again.'
        );
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;

        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(new NoSuchEntityException());

        $this->attributeManagement->assign(
            $entityTypeCode,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    public function testAssignInputException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The attribute set ID is incorrect. Verify the ID and try again.');
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode+1);

        $this->attributeManagement->assign(
            $entityTypeCode,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    public function testAssignInputExceptionGroupInSet()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The attribute group doesn\'t belong to the attribute set.');
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode);

        $attributeGroup = $this->getMockBuilder(AttributeGroupInterface::class)
            ->setMethods(['getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupRepositoryMock->expects($this->once())->method('get')->willReturn($attributeGroup);
        $attributeGroup->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId + 1);

        $this->attributeManagement->assign(
            $entityTypeCode,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    public function testAssign()
    {
        $entityTypeCode = 1;
        $attributeSetId = 2;
        $attributeGroupId = 3;
        $attributeCode = 4;
        $sortOrder = 5;
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn($entityTypeCode);
        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($entityTypeCode, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn(16);
        $this->attributeResourceMock->expects($this->once())->method('saveInSetIncluding')
            ->with(
                $attributeMock,
                16,
                $attributeSetId,
                $attributeGroupId,
                $sortOrder
            )
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getData')->with('entity_attribute_id')->willReturnSelf();

        $attributeGroup = $this->getMockBuilder(AttributeGroupInterface::class)
            ->setMethods(['getAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupRepositoryMock->expects($this->once())->method('get')->willReturn($attributeGroup);
        $attributeGroup->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);

        $this->assertEquals(
            $attributeMock,
            $this->attributeManagement->assign(
                $entityTypeCode,
                $attributeSetId,
                $attributeGroupId,
                $attributeCode,
                $sortOrder
            )
        );
    }

    public function testUnassign()
    {
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->addMethods(['getEntityAttributeId'])
            ->onlyMethods(
                ['setAttributeSetId', 'loadEntityAttributeIdBySet', 'getIsUserDefined', 'deleteEntity']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn(33);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with(33)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(12);
        $attributeMock->expects($this->once())->method('getIsUserDefined')->willReturn(true);
        $attributeMock->expects($this->once())->method('deleteEntity')->willReturnSelf();

        $this->assertTrue($this->attributeManagement->unassign($attributeSetId, $attributeCode));
    }

    public function testUnassignInputException()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->addMethods(['getEntityAttributeId'])
            ->onlyMethods(
                ['setAttributeSetId', 'loadEntityAttributeIdBySet', 'getIsUserDefined', 'deleteEntity']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(null);
        $attributeMock->expects($this->never())->method('getIsUserDefined');
        $attributeMock->expects($this->never())->method('deleteEntity');

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);

        $this->expectExceptionMessage(
            'The "code" attribute wasn\'t found in the "1" attribute set. Enter the attribute and try again.'
        );
    }

    public function testUnassignWithWrongAttributeSet()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The "1234567" attribute set wasn\'t found. Verify and try again.');
        $attributeSetId = 1234567;
        $attributeCode = 'code';

        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(new NoSuchEntityException(__('hello')));

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    public function testUnassignStateException()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The system attribute can\'t be deleted.');
        $attributeSetId = 1;
        $attributeCode = 'code';

        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->entityTypeFactoryMock->expects($this->once())->method('create')->willReturn($this->eavConfigMock);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(66);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())->method('getEntityType')->with(66)->willReturn($entityTypeMock);
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute::class)
            ->addMethods(['getEntityAttributeId'])
            ->onlyMethods(
                ['setAttributeSetId', 'loadEntityAttributeIdBySet', 'getIsUserDefined', 'deleteEntity']
            )
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())->method('getEntityTypeCode')->willReturn('entity type code');
        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('entity type code', $attributeCode)
            ->willReturn($attributeMock);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with($attributeSetId)->willReturnSelf();
        $attributeMock->expects($this->once())->method('loadEntityAttributeIdBySet')->willReturnSelf();
        $attributeMock->expects($this->once())->method('getEntityAttributeId')->willReturn(12);
        $attributeMock->expects($this->once())->method('getIsUserDefined')->willReturn(null);
        $attributeMock->expects($this->never())->method('deleteEntity');

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    public function testGetAttributes()
    {
        $entityType = 'type';
        $attributeSetId = 148;

        $attributeCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $attributeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeCollectionMock);

        $objectManager = new ObjectManager($this);
        $objectManager->setBackwardCompatibleProperty(
            $this->attributeManagement,
            'attributeCollectionFactory',
            $attributeCollectionFactoryMock
        );

        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityType)
            ->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn(88);
        $attributeSetMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn(88);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(88);
        $this->attributeCollectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with(88)
            ->willReturnSelf();
        $attributeMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $this->attributeCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->attributeCollectionMock->expects($this->once())->method('getItems')->willReturn([$attributeMock]);

        $this->assertEquals([$attributeMock], $this->attributeManagement->getAttributes($entityType, $attributeSetId));
    }

    public function testGetAttributesNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with attributeSetId = 148');
        $entityType = 'type';
        $attributeSetId = 148;

        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $entityTypeMock = $this->createMock(Type::class);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityType)
            ->willReturn($entityTypeMock);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn(77);
        $attributeSetMock->expects($this->once())->method('getAttributeSetId')->willReturn(88);
        $attributeSetMock->expects($this->once())->method('getEntityTypeId')->willReturn(88);

        $this->attributeManagement->getAttributes($entityType, $attributeSetId);
    }
}

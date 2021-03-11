<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit\Sequence;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Sequence\SequenceApplier;
use Magento\Framework\EntityManager\Sequence\SequenceManager;
use Magento\Framework\EntityManager\Sequence\SequenceRegistry;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\DB\Sequence\SequenceInterface;

class SequenceApplierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    /**
     * @var TypeResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $typeResolverMock;

    /**
     * @var SequenceManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var SequenceRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sequenceRegistryMock;

    /**
     * @var HydratorPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hydratorPoolMock;

    /**
     * @var DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityMock;

    /**
     * @var HydratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hydratorMock;

    /**
     * @var EntityMetadataInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataMock;

    /**
     * @var SequenceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $sequenceMock;

    /**
     * @var SequenceApplier
     */
    private $sequenceApplier;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeResolverMock = $this->getMockBuilder(TypeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceManagerMock = $this->getMockBuilder(SequenceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceRegistryMock = $this->getMockBuilder(SequenceRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydratorPoolMock = $this->getMockBuilder(HydratorPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydratorMock = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sequenceMock = $this->getMockBuilder(SequenceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sequenceApplier = $helper->getObject(
            SequenceApplier::class,
            [
                'metadataPool' => $this->metadataPoolMock,
                'typeResolver' => $this->typeResolverMock,
                'sequenceManager' => $this->sequenceManagerMock,
                'sequenceRegistry' => $this->sequenceRegistryMock,
                'hydratorPool' => $this->hydratorPoolMock
            ]
        );
    }

    public function testApplySequenceIsNull()
    {
        $entityType = 'entity_type';
        $this->typeResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->entityMock)
            ->willReturn($entityType);
        $this->sequenceRegistryMock->expects($this->once())->method('retrieve')->with($entityType)->willReturn(null);
        $this->assertEquals($this->entityMock, $this->sequenceApplier->apply($this->entityMock));
    }

    public function testApplyEntityHasIdentifier()
    {
        $entityType = 'entity_type';
        $identifierField = 'identifier_field';
        $entityData = [$identifierField => 'data'];
        $sequenceInfo = ['sequence' => $this->sequenceMock];
        $this->typeResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->entityMock)
            ->willReturn($entityType);
        $this->sequenceRegistryMock->expects($this->exactly(2))
            ->method('retrieve')
            ->with($entityType)
            ->willReturn($sequenceInfo);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($this->metadataMock);
        $this->hydratorPoolMock->expects($this->once())
            ->method('getHydrator')
            ->with($entityType)
            ->willReturn($this->hydratorMock);
        $this->hydratorMock->expects($this->once())
            ->method('extract')
            ->with($this->entityMock)
            ->willReturn($entityData);
        $this->metadataMock->expects($this->any())->method('getIdentifierField')->willReturn($identifierField);
        $this->sequenceManagerMock->expects($this->once())
            ->method('force')
            ->with(
                $entityType,
                $entityData[$identifierField]
            );

        $this->assertEquals($this->entityMock, $this->sequenceApplier->apply($this->entityMock));
    }

    public function testApplyEntityDoesNotHaveIdentifier()
    {
        $entityType = 'entity_type';
        $identifierField = 'identifier_field';
        $identifierFieldEmptyValue = '';
        $entityData = [$identifierField => $identifierFieldEmptyValue];
        $sequenceInfo = ['sequence' => $this->sequenceMock];
        $this->typeResolverMock->expects($this->once())
            ->method('resolve')
            ->with($this->entityMock)
            ->willReturn($entityType);
        $this->sequenceRegistryMock->expects($this->exactly(2))
            ->method('retrieve')
            ->with($entityType)
            ->willReturn($sequenceInfo);
        $this->metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($this->metadataMock);
        $this->hydratorPoolMock->expects($this->once())
            ->method('getHydrator')
            ->with($entityType)
            ->willReturn($this->hydratorMock);
        $this->hydratorMock->expects($this->once())
            ->method('extract')
            ->with($this->entityMock)
            ->willReturn($entityData);
        $this->metadataMock->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn($identifierFieldEmptyValue);
        $nextValue = 'next_value_data';
        $this->sequenceMock->expects($this->once())->method('getNextValue')->willReturn($nextValue);
        $entityData[''] = $nextValue;
        $this->hydratorMock->expects($this->once())
            ->method('hydrate')
            ->with($this->entityMock, $entityData)
            ->willReturn($this->entityMock);

        $this->assertEquals($this->entityMock, $this->sequenceApplier->apply($this->entityMock));
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\EntitySnapshot;
use Magento\Framework\Model\EntitySnapshot\AttributeProvider;

/**
 * Class EntitySnapshotTest
 */
class EntitySnapshotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntitySnapshot
     */
    protected $entitySnapshot;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeProviderMock;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeProviderMock = $this->getMockBuilder(AttributeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entitySnapshot = new EntitySnapshot(
            $this->metadataPoolMock,
            $this->attributeProviderMock
        );
    }

    public function testIsModified()
    {
        $entityType = "type";
        $entity = new \stdClass();
        $entity->id = 1;
        $entity->test = 21;
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->atLeastOnce())->method('getMetadata')->willReturn($metadata);
        $this->metadataPoolMock->expects($this->atLeastOnce())->method('getHydrator')->willReturn($entityHydrator);
        $entityHydrator->expects($this->at(0))->method('extract')
            ->with($entity)
            ->willReturn(get_object_vars($entity));
        $entityHydrator->expects($this->at(1))->method('extract')
            ->with($entity)
            ->willReturn(get_object_vars($entity));
        $entityModified = clone $entity;
        $entityModified->id = null;
        $entityHydrator->expects($this->at(2))->method('extract')
        ->with($entity)
        ->willReturn(get_object_vars($entityModified));
        $metadata->expects($this->atLeastOnce())->method('getIdentifierField')->willReturn('id');
        $this->attributeProviderMock->expects($this->once())->method('getAttributes')->willReturn([]);

        $this->entitySnapshot->registerSnapshot($entityType, $entity);
        $this->assertFalse($this->entitySnapshot->isModified($entityType, $entity));
        $this->assertTrue($this->entitySnapshot->isModified($entityType, $entity));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\EntitySnapshot;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\EntitySnapshot\AttributeProvider;
use Magento\Framework\Model\EntitySnapshot\AttributeProviderInterface;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $concreteAttributeProviderMock;

    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var AttributeProvider
     */
    protected $attributeProvider;

    protected function setUp(): void
    {
        $this->concreteAttributeProviderMock = $this->getMockBuilder(
            AttributeProviderInterface::class
        )->getMockForAbstractClass();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeProvider = new AttributeProvider(
            $this->metadataPoolMock,
            $this->objectManagerMock,
            ['default' => ['eav' => AttributeProviderInterface::class]]
        );
    }

    public function testGetAttributes()
    {
        $entityType = 'Product';
        $entityTable = 'entity_table';
        $linkField = 'some_id';
        $identifierField = 'id';
        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributes = ['test' => 1];
        $this->metadataPoolMock->expects($this->atLeastOnce())->method('getMetadata')->willReturn($metadata);
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $metadata->expects($this->once())->method('getEntityConnection')->willReturn($connection);
        $metadata->expects($this->once())->method('getEntityTable')->willReturn($entityTable);
        $metadata->expects($this->exactly(2))->method('getLinkField')->willReturn($linkField);
        $metadata->expects($this->once())->method('getIdentifierField')->willReturn($identifierField);
        $connection->expects($this->once())->method('describeTable')->with($entityTable)->willReturn([]);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(AttributeProviderInterface::class)
            ->willReturn($this->concreteAttributeProviderMock);
        $this->concreteAttributeProviderMock->expects($this->once())->method('getAttributes')
            ->with($entityType)
            ->willReturn($attributes);
        $this->assertEquals($attributes, $this->attributeProvider->getAttributes($entityType));
    }
}

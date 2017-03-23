<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\EntitySnapshot;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\Model\EntitySnapshot\AttributeProvider;
use Magento\Framework\Model\EntitySnapshot\AttributeProviderInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Class AttributeProviderTest
 */
class AttributeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $concreteAttributeProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var AttributeProvider
     */
    protected $attributeProvider;

    protected function setUp()
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
        $connection = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
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

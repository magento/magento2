<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Model\Entity\SequenceFactory;

/**
 * Class MetadataPoolTest
 */
class MetadataPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadataMock;

    /**
     * @var \Magento\Framework\Model\Entity\SequenceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sequenceFactoryMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'get', 'configure'])
            ->getMock();
        $this->sequenceFactoryMock = $this->getMockBuilder(SequenceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->entityMetadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider metadataProvider
     * @param string $entityType
     * @param array $metadata
     * @return void
     */
    public function testGetMetadata($entityType, $metadata)
    {
        $sequence = $this->getMockBuilder(
            'Magento\Framework\DB\Sequence\SequenceInterface'
        )->disableOriginalConstructor();

        $defaults = [
            'connectionName' => 'default',
            'eavEntityType' => null,
            'entityContext' => [],
            'sequence' => $sequence,
            'fields' => null
        ];

        $finalMetadata = $metadata;
        $finalMetadata[$entityType]['connectionName'] = 'default';

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(EntityMetadata::class, array_merge($defaults, $metadata[$entityType]))
            ->willReturn($this->entityMetadataMock);
        $this->sequenceFactoryMock->expects($this->once())
            ->method('create')
            ->with($entityType, $finalMetadata)
            ->willReturn($sequence);
        $metadataPool = new MetadataPool(
            $this->objectManagerMock,
            $this->sequenceFactoryMock,
            $metadata
        );
        $this->assertEquals($this->entityMetadataMock, $metadataPool->getMetadata($entityType));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Not enough configuration
     */
    public function testGetMetadataThrowsException()
    {
        $metadataPool = new MetadataPool(
            $this->objectManagerMock,
            $this->sequenceFactoryMock,
            []
        );
        $this->assertNotEquals($this->entityMetadataMock, $metadataPool->getMetadata('testType'));
    }

    public function testHydrator()
    {
        $metadataPool = new MetadataPool(
            $this->objectManagerMock,
            $this->sequenceFactoryMock,
            []
        );
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($entityHydrator);
        $this->assertEquals($entityHydrator, $metadataPool->getHydrator('testType'));
    }

    /**
     * @return array
     */
    public function metadataProvider()
    {
        return [
            [
                'SomeNameSpace\TestInterface',
                [
                    'SomeNameSpace\TestInterface' =>  [
                        'entityTableName' => 'testTable',
                        'identifierField' => 'testId'
                    ]
                ]
            ],
            [
                'SomeNameSpace\TestInterface',
                [
                    'SomeNameSpace\TestInterface' =>  [
                        'entityTableName' => 'testTable',
                        'identifierField' => 'testId',
                        'entityContext' => ['store_id']
                    ]
                ]
            ],
            [
                'SomeNameSpace\TestInterface',
                [
                    'SomeNameSpace\TestInterface' =>  [
                        'entityTableName' => 'testTable',
                        'identifierField' => 'testId',
                        'entityContext' => ['store_id'],
                        'eavEntityType' => 'SomeEavType',
                        'fields' => ['field1']
                    ]
                ]
            ]
        ];
    }
}

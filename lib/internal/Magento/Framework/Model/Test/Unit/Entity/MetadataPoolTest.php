<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity;

use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\EntityMetadata;

/**
 * Class MetadataPoolTest
 */
class MetadataPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Entity\EntityMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadataFactoryMock;

    /**
     * @var \Magento\Framework\Model\Entity\EntityHydratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityHydratorFactoryMock;

    /**
     * @var EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityMetadataMock;

    protected function setUp()
    {
        $this->entityMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Model\Entity\EntityMetadataFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->entityHydratorFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Model\Entity\EntityHydratorFactory'
        )->disableOriginalConstructor()
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
        $defaults = [
            'connectionName' => 'default',
            'eavEntityType' => null,
            'sequence' => null,
            'entityContext' => [],
            'fields' => null
        ];
        $this->entityMetadataFactoryMock->expects($this->once())
            ->method('create')
            ->with(array_merge($defaults, $metadata[$entityType]))
            ->willReturn($this->entityMetadataMock);
        $metadataPool = new MetadataPool(
            $this->entityMetadataFactoryMock,
            $this->entityHydratorFactoryMock,
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
            $this->entityMetadataFactoryMock,
            $this->entityHydratorFactoryMock,
            []
        );
        $this->assertNotEquals($this->entityMetadataMock, $metadataPool->getMetadata('testType'));
    }

    public function testHydrator()
    {
        $metadataPool = new MetadataPool(
            $this->entityMetadataFactoryMock,
            $this->entityHydratorFactoryMock,
            []
        );
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityHydratorFactoryMock->expects($this->once())->method('create')->willReturn($entityHydrator);
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
            ]
            ,
            [
                'SomeNameSpace\TestInterface',
                [
                    'SomeNameSpace\TestInterface' =>  [
                        'entityTableName' => 'testTable',
                        'identifierField' => 'testId',
                        'entityContext' => ['store_id'],
                        'eavEntityType' => 'SomeEavType',
                        'fields' => ['field1'],
                        'sequence' => 'sq1'
                    ]
                ]
            ]
        ];
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Option;

use \Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Value;

/**
 * Class CollectionTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Value\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionsFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $joinProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock(
            'Magento\Framework\Data\Collection\EntityFactory', ['create'], [], '', false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategy\Query', ['fetchAll'], [], '', false
        );
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->optionsFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $this->joinProcessor = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Option',
            ['getConnection', '__wakeup', 'getMainTable', 'getTable'],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock('Magento\Framework\DB\Select', ['from', 'reset', 'join'], [], '', false);
        $this->connection =
            $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', ['select'], [], '', false);
        $this->connection->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));
        $this->resourceMock->expects($this->once())
            ->method('getMainTable')
            ->will($this->returnValue('test_main_table'));
        $this->resourceMock->expects($this->exactly(3))
            ->method('getTable')
            ->withConsecutive(
                ['test_main_table'],
                ['catalog_product_entity'],
                ['catalog_product_entity']
            )->willReturnOnConsecutiveCalls(
                $this->returnValue('test_main_table'),
                'catalog_product_entity',
                'catalog_product_entity'
            );
        $this->metadataPoolMock = $this->getMock('Magento\Framework\EntityManager\MetadataPool', [], [], '', false);
        $metadata = $this->getMock('Magento\Framework\EntityManager\EntityMetadata', [], [], '', false);
        $metadata->expects($this->any())->method('getLinkField')->willReturn('id');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $this->selectMock->expects($this->exactly(2))->method('join');

        $this->prepareObjectManager([
            ['Magento\Framework\EntityManager\MetadataPool', $this->metadataPoolMock],
            ['Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface', $this->joinProcessor]
        ]);

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->optionsFactoryMock,
            $this->storeManagerMock,
            null,
            $this->resourceMock
        );
    }

    public function testReset()
    {
        $this->collection->reset();
    }

    /**
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass('Magento\Framework\App\ObjectManager');
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}

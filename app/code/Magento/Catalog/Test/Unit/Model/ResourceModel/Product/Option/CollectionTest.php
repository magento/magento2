<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Option;

use \Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use \Magento\Catalog\Model\ResourceModel\Product\Option\Value;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Value\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionsFactoryMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $joinProcessor;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->entityFactoryMock = $this->createPartialMock(
            \Magento\Framework\Data\Collection\EntityFactory::class,
            ['create']
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->fetchStrategyMock = $this->createPartialMock(
            \Magento\Framework\Data\Collection\Db\FetchStrategy\Query::class,
            ['fetchAll']
        );
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $this->optionsFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->joinProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->resourceMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option::class,
            ['getConnection', '__wakeup', 'getMainTable', 'getTable']
        );
        $this->selectMock = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['from', 'reset', 'join']);
        $this->connection =
            $this->createPartialMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, ['select']);
        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->resourceMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('test_main_table');
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
        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $metadata = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);
        $metadata->expects($this->any())->method('getLinkField')->willReturn('id');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $this->selectMock->expects($this->exactly(2))->method('join');

        $this->collection = new Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->optionsFactoryMock,
            $this->storeManagerMock,
            null,
            $this->resourceMock,
            $this->metadataPoolMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'joinProcessor',
            $this->joinProcessor
        );
    }

    public function testReset()
    {
        $this->collection->reset();
    }
}

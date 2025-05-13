<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Option;

use Magento\Catalog\Model\ResourceModel\Product\Option;
use Magento\Catalog\Model\ResourceModel\Product\Option\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPoolMock;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Value\CollectionFactory|MockObject
     */
    protected $optionsFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    protected $joinProcessor;

    /**
     * @var Option|MockObject
     */
    protected $resourceMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->entityFactoryMock = $this->createPartialMock(
            EntityFactory::class,
            ['create']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->fetchStrategyMock = $this->createPartialMock(
            Query::class,
            ['fetchAll']
        );
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->optionsFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->joinProcessor = $this->getMockBuilder(
            JoinProcessorInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceMock = $this->createPartialMock(
            Option::class,
            ['getConnection', 'getMainTable', 'getTable']
        );
        $this->selectMock = $this->createPartialMock(Select::class, ['from', 'reset', 'join']);
        $this->connection =
            $this->createPartialMock(Mysql::class, ['select']);
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
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['test_main_table'] => $this->returnValue('test_main_table'),
                ['catalog_product_entity'] => 'catalog_product_entity'
            });
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadata = $this->createMock(EntityMetadata::class);
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

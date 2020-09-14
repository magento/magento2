<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Grid;

use Magento\Cms\Model\ResourceModel\Page\Grid\Collection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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
     * @var EntityFactoryInterface|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var AggregationInterface|MockObject
     */
    protected $aggregationsMock;

    /**
     * @var Select
     */
    protected $selectMock;

    /**
     * @var Collection
     */
    protected $model;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->getMockBuilder(EntityFactoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $this->fetchStrategyMock = $this->getMockBuilder(FetchStrategyInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->aggregationsMock = $this->getMockBuilder(AggregationInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->model = (new ObjectManager($this))->getObject(Collection::class, [
            'entityFactory' => $this->entityFactoryMock,
            'logger' => $this->loggerMock,
            'fetchStrategy' => $this->fetchStrategyMock,
            'eventManager' => $this->eventManagerMock,
            'storeManager' => $this->storeManagerMock,
            'metadataPool' => $this->metadataPoolMock,
            'mainTable' => null,
            'eventPrefix' => 'test_event_prefix',
            'eventObject' => 'test_event_object',
            'resourceModel' => null,
            'resource' => $this->resourceMock,
        ]);
    }

    public function testSetterGetter()
    {
        $this->model->setAggregations($this->aggregationsMock);
        $this->assertInstanceOf(AggregationInterface::class, $this->model->getAggregations());
    }

    public function testSetSearchCriteria()
    {
        $this->assertEquals($this->model, $this->model->setSearchCriteria());
    }
}

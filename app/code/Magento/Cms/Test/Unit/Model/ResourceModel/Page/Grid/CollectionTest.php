<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\ResourceModel\Page\Grid;

use Magento\Cms\Model\ResourceModel\Page\Grid\Collection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;

/**
 * Class CollectionTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var AggregationInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Group\Grid;

use Magento\Customer\Model\ResourceModel\Group\Grid\Collection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * CollectionTest contains unit tests for \Magento\Customer\Model\ResourceModel\Group\Grid\Collection class
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

    /**
     * SetUp method
     *
     * @return void
     */
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
            'mainTable' => null,
            'eventPrefix' => 'test_event_prefix',
            'eventObject' => 'test_event_object',
            'resourceModel' => null,
            'resource' => $this->resourceMock,
        ]);
    }

    /**
     * @covers \Magento\Customer\Model\ResourceModel\Group\Grid\Collection::setSearchCriteria
     * @covers \Magento\Customer\Model\ResourceModel\Group\Grid\Collection::getAggregations
     */
    public function testSetGetAggregations()
    {
        $this->model->setAggregations($this->aggregationsMock);
        $this->assertInstanceOf(AggregationInterface::class, $this->model->getAggregations());
    }

    /**
     * @covers \Magento\Customer\Model\ResourceModel\Group\Grid\Collection::setSearchCriteria
     */
    public function testSetSearchCriteria()
    {
        $this->assertEquals($this->model, $this->model->setSearchCriteria());
    }
}

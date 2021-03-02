<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Report;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Report\Collection
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategy;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $reportResource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ruleFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->entityFactory = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);

        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->fetchStrategy = $this->createMock(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class);

        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->reportResource = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Report::class,
            ['getConnection', 'getMainTable']
        );

        $this->connection = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['select', 'getDateFormatSql', 'quoteInto']
        );

        $this->selectMock = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['from', 'where', 'group']);

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->reportResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->reportResource->expects($this->any())
            ->method('getMainTable')
            ->willReturn('test_main_table');

        $this->ruleFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Report\RuleFactory::class,
            ['create']
        );

        $this->object = new \Magento\SalesRule\Model\ResourceModel\Report\Collection(
            $this->entityFactory,
            $this->loggerMock,
            $this->fetchStrategy,
            $this->eventManager,
            $this->reportResource,
            $this->ruleFactory
        );
    }

    public function testAddRuleFilter()
    {
        $this->assertInstanceOf(get_class($this->object), $this->object->addRuleFilter([]));
    }

    public function testApplyAggregatedTableNegativeIsTotals()
    {
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with($this->equalTo([null, 'coupon_code']));
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    public function testApplyAggregatedTableIsSubTotals()
    {
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with($this->equalTo(null));

        $this->object->setIsSubTotals(true);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    public function testApplyRulesFilterNoRulesIdsFilter()
    {
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    public function testApplyRulesFilterEmptyRulesList()
    {
        $rulesList = [];
        $ruleMock = $this->getRuleMock();
        $ruleMock->expects($this->once())
            ->method('getUniqRulesNamesList')
            ->willReturn($rulesList);

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $ruleFilter = [1,2,3];
        $this->object->addRuleFilter($ruleFilter);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    public function testApplyRulesFilterWithRulesList()
    {
        $rulesList = [1 => 'test rule 1', 10 => 'test rule 10', 30 => 'test rule 30'];
        $this->connection->expects($this->at(1))
            ->method('quoteInto')
            ->with($this->equalTo('rule_name = ?'), $this->equalTo($rulesList[1]))
            ->willReturn('test_1');
        $this->connection->expects($this->at(2))
            ->method('quoteInto')
            ->with($this->equalTo('rule_name = ?'), $this->equalTo($rulesList[30]))
            ->willReturn('test_2');

        $this->selectMock->expects($this->at(3))
            ->method('where')
            ->with($this->equalTo(implode(' OR ', [
                'test_1',
                'test_2',
            ])));

        $ruleMock = $this->getRuleMock();
        $ruleMock->expects($this->once())
            ->method('getUniqRulesNamesList')
            ->willReturn($rulesList);

        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $ruleFilter = [1,2,30];
        $this->object->addRuleFilter($ruleFilter);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRuleMock()
    {
        return $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Report\Rule::class,
            ['getUniqRulesNamesList']
        );
    }
}

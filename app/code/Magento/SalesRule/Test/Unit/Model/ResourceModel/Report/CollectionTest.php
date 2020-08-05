<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel\Report;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Report;
use Magento\SalesRule\Model\ResourceModel\Report\Collection;
use Magento\SalesRule\Model\ResourceModel\Report\Rule;
use Magento\SalesRule\Model\ResourceModel\Report\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $object;

    /**
     * @var MockObject
     */
    protected $entityFactory;

    /**
     * @var MockObject
     */
    protected $loggerMock;

    /**
     * @var MockObject
     */
    protected $fetchStrategy;

    /**
     * @var MockObject
     */
    protected $eventManager;

    /**
     * @var MockObject
     */
    protected $reportResource;

    /**
     * @var MockObject
     */
    protected $ruleFactory;

    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var MockObject
     */
    protected $selectMock;

    protected function setUp(): void
    {
        $this->entityFactory = $this->createMock(EntityFactory::class);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->fetchStrategy = $this->getMockForAbstractClass(FetchStrategyInterface::class);

        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->reportResource = $this->createPartialMock(
            Report::class,
            ['getConnection', 'getMainTable']
        );

        $this->connection = $this->createPartialMock(
            Mysql::class,
            ['select', 'getDateFormatSql', 'quoteInto']
        );

        $this->selectMock = $this->createPartialMock(Select::class, ['from', 'where', 'group']);

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
            RuleFactory::class,
            ['create']
        );

        $this->object = new Collection(
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
            ->with([null, 'coupon_code']);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    public function testApplyAggregatedTableIsSubTotals()
    {
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with(null);

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
            ->with('rule_name = ?', $rulesList[1])
            ->willReturn('test_1');
        $this->connection->expects($this->at(2))
            ->method('quoteInto')
            ->with('rule_name = ?', $rulesList[30])
            ->willReturn('test_2');

        $this->selectMock->expects($this->at(3))
            ->method('where')
            ->with(implode(' OR ', ['test_1', 'test_2']));

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
     * @return MockObject
     */
    protected function getRuleMock()
    {
        return $this->createPartialMock(
            Rule::class,
            ['getUniqRulesNamesList']
        );
    }
}

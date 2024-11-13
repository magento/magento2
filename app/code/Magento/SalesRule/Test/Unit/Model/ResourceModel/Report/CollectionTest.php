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

    /**
     * @inheritDoc
     */
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

    /**
     * @return void
     */
    public function testAddRuleFilter(): void
    {
        $this->assertInstanceOf(get_class($this->object), $this->object->addRuleFilter([]));
    }

    /**
     * @return void
     */
    public function testApplyAggregatedTableNegativeIsTotals(): void
    {
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with([null, 'coupon_code']);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    /**
     * @return void
     */
    public function testApplyAggregatedTableIsSubTotals(): void
    {
        $this->selectMock->expects($this->once())
            ->method('group')
            ->with(null);

        $this->object->setIsSubTotals(true);
        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    /**
     * @return void
     */
    public function testApplyRulesFilterNoRulesIdsFilter(): void
    {
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertInstanceOf(get_class($this->object), $this->object->loadWithFilter());
    }

    /**
     * @return void
     */
    public function testApplyRulesFilterEmptyRulesList(): void
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

    /**
     * @return void
     */
    public function testApplyRulesFilterWithRulesList(): void
    {
        $rulesList = [1 => 'test rule 1', 10 => 'test rule 10', 30 => 'test rule 30'];
        $this->connection
            ->method('quoteInto')
            ->willReturnCallback(function ($arg1, $arg2) use ($rulesList) {
                if ($arg1 == 'rule_name = ?' && $arg2 = $rulesList[1]) {
                    return 'test_1';
                } elseif ($arg1 == 'rule_name = ?' && $arg2 = $rulesList[30]) {
                    return 'test_2';
                }
            });
        $this->selectMock
            ->method('where')
            ->willReturnCallback(function ($arg1) {
                if (is_null($arg1) || $arg1 == implode(' OR ', ['test_1', 'test_2'])) {
                    return null;
                }
            });

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
    protected function getRuleMock(): MockObject
    {
        return $this->createPartialMock(
            Rule::class,
            ['getUniqRulesNamesList']
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Mview\Config;
use Magento\Framework\Mview\View\AdditionalColumnsProcessor\DefaultProcessor;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\CollectionInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\View\SubscriptionStatementPostprocessorInterface;
use Magento\Framework\Mview\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionTest extends TestCase
{
    /**
     * Mysql PDO DB adapter mock
     *
     * @var MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /**
     * @var Subscription
     */
    protected $model;

    /**
     * @var MockObject|ResourceConnection
     */
    protected $resourceMock;

    /**
     * @var MockObject|TriggerFactory
     */
    protected $triggerFactoryMock;

    /**
     * @var MockObject|CollectionInterface
     */
    protected $viewCollectionMock;

    /**
     * @var MockObject|ViewInterface
     */
    protected $viewMock;

    /**
     * @var  string
     */
    private $tableName;

    /**
     * @var DefaultProcessor|MockObject
     */
    private $defaultProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->tableName = 'test_table';
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->defaultProcessor = $this->createMock(DefaultProcessor::class);
        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        ObjectManager::getInstance()->expects($this->any())
            ->method('get')
            ->with(DefaultProcessor::class)
            ->willReturn($this->defaultProcessor);
        $this->triggerFactoryMock = $this->createMock(TriggerFactory::class);
        $this->viewCollectionMock = $this->getMockForAbstractClass(
            CollectionInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->viewMock = $this->getMockForAbstractClass(
            ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->viewMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->resourceMock->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);
        $mviewConfigMock = $this->createMock(Config::class);
        $mviewConfigMock->expects($this->any())
            ->method('getView')
            ->willReturn([
                'subscriptions' => [
                    $this->tableName => [
                        'processor' => DefaultProcessor::class
                    ]
                ]
            ]);
        $statementPostprocessorMock = $this->createMock(SubscriptionStatementPostprocessorInterface::class);
        $statementPostprocessorMock->method('process')
            ->willReturnArgument(2);
        $this->model = new Subscription(
            $this->resourceMock,
            $this->triggerFactoryMock,
            $this->viewCollectionMock,
            $this->viewMock,
            $this->tableName,
            'columnName',
            [],
            [],
            $mviewConfigMock,
            $statementPostprocessorMock
        );
    }

    /**
     * @return void
     */
    public function testGetView(): void
    {
        $this->assertEquals($this->viewMock, $this->model->getView());
    }

    /**
     * @return void
     */
    public function testGetTableName(): void
    {
        $this->assertEquals($this->tableName, $this->model->getTableName());
    }

    /**
     * @return void
     */
    public function testGetColumnName(): void
    {
        $this->assertEquals('columnName', $this->model->getColumnName());
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate(): void
    {
        $triggerName = 'trigger_name';
        $this->resourceMock->expects($this->atLeastOnce())->method('getTriggerName')->willReturn($triggerName);
        $triggerMock = $this->getMockBuilder(Trigger::class)
            ->onlyMethods(['setName', 'getName', 'setTime', 'setEvent', 'setTable', 'addStatement'])
            ->disableOriginalConstructor()
            ->getMock();
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->with($triggerName)->willReturnSelf();
        $triggerMock->expects($this->any())
            ->method('getName')
            ->willReturn('triggerName');
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(Trigger::TIME_AFTER)->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)->willReturnSelf();

        $triggerMock
            ->method('addStatement')
            ->withConsecutive(
                ["INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);"],
                ["INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);"],
                ["INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);"],
                ["INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);"],
                ["INSERT IGNORE INTO test_view_cl (entity_id) VALUES (OLD.columnName);"],
                ["INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (OLD.columnName);"]
            )->willReturn($triggerMock);

        $changelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $changelogMock->expects($this->any())
            ->method('getName')
            ->willReturn('test_view_cl');
        $changelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $this->viewMock->expects($this->atLeastOnce())
            ->method('getChangelog')
            ->willReturn($changelogMock);

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($triggerMock);

        $otherChangelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->any())
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->getMockForAbstractClass(
            ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherViewMock->expects($this->exactly(1))
            ->method('getId')
            ->willReturn('other_id');
        $otherViewMock->expects($this->exactly(4))
            ->method('getSubscriptions')
            ->willReturn(
                [
                    $this->tableName => ['name' => $this->tableName, 'column' => 'columnName'],
                    'otherTableName' => ['name' => 'otherTableName', 'column' => 'columnName']
                ]
            );
        $otherViewMock->expects($this->atLeastOnce())
            ->method('getChangelog')
            ->willReturn($otherChangelogMock);

        $this->viewMock->expects($this->any())
            ->method('getId')
            ->willReturn('this_id');

        $this->viewCollectionMock->expects($this->once())
            ->method('getViewsByStateMode')
            ->with(StateInterface::MODE_ENABLED)
            ->willReturn([$this->viewMock, $otherViewMock]);

        $this->connectionMock->expects($this->exactly(3))
            ->method('dropTrigger')
            ->with('triggerName')
            ->willReturn(true);
        $this->connectionMock->expects($this->exactly(3))
            ->method('createTrigger')
            ->with($triggerMock);

        $this->viewMock->expects($this->exactly(3))
            ->method('getSubscriptions')
            ->willReturn(
                [
                    $this->tableName => ['name' => $this->tableName, 'column' => 'columnName'],
                    'otherTableName' => ['name' => 'otherTableName', 'column' => 'columnName']
                ]
            );

        $this->model->create();
    }

    /**
     * @return void
     */
    public function testRemove(): void
    {
        $triggerMock = $this->createMock(Trigger::class);
        $triggerMock->expects($this->exactly(3))
            ->method('setName')->willReturnSelf();
        $triggerMock->expects($this->any())
            ->method('getName')
            ->willReturn('triggerName');
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(Trigger::TIME_AFTER)->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('addStatement')->willReturnSelf();

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($triggerMock);

        $otherChangelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->any())
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->getMockForAbstractClass(
            ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherViewMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('other_id');
        $otherViewMock->expects($this->atLeastOnce())
            ->method('getChangelog')
            ->willReturn($otherChangelogMock);

        $this->viewMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn('this_id');
        $otherViewMock->expects($this->atLeastOnce())
            ->method('getSubscriptions')
            ->willReturn(
                [
                    $this->tableName => ['name' => $this->tableName, 'column' => 'columnName'],
                    'otherTableName' => ['name' => 'otherTableName', 'column' => 'columnName']
                ]
            );

        $this->viewCollectionMock->expects($this->exactly(1))
            ->method('getViewsByStateMode')
            ->with(StateInterface::MODE_ENABLED)
            ->willReturn([$this->viewMock, $otherViewMock]);

        $this->connectionMock->expects($this->exactly(3))
            ->method('dropTrigger')
            ->with('triggerName')
            ->willReturn(true);

        $triggerMock->expects($this->exactly(3))
            ->method('getStatements')
            ->willReturn(true);

        $this->connectionMock->expects($this->exactly(3))
            ->method('createTrigger')
            ->with($triggerMock);

        $this->model->remove();
    }

    /**
     * Test ignored columns for mview specified at the subscription level.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testBuildStatementIgnoredColumnSubscriptionLevel(): void
    {
        $tableName = 'cataloginventory_stock_item';
        $ignoredColumnName = 'low_stock_date';
        $notIgnoredColumnName = 'backorders';
        $viewId = 'cataloginventory_stock';
        $ignoredData = [
            $viewId => [
                $tableName => [
                    $ignoredColumnName => true,
                    $notIgnoredColumnName => false
                ]
            ]
        ];
        $mviewConfigMock = $this->createMock(Config::class);
        $mviewConfigMock->expects($this->any())
            ->method('getView')
            ->willReturn([
                'subscriptions' => [
                    $tableName => [
                        'processor' => DefaultProcessor::class
                    ]
                ]
            ]);
        $statementPostprocessorMock = $this->createMock(SubscriptionStatementPostprocessorInterface::class);
        $statementPostprocessorMock->method('process')
            ->willReturnArgument(2);

        $this->connectionMock->expects($this->any())
            ->method('isTableExists')
            ->with('cataloginventory_stock_item')
            ->willReturn(true);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->with($tableName)
            ->willReturn([
                'item_id' => ['COLUMN_NAME' => 'item_id'],
                'product_id' => ['COLUMN_NAME' => 'product_id'],
                'stock_id' => ['COLUMN_NAME' => 'stock_id'],
                'qty' => ['COLUMN_NAME' => 'qty'],
                $ignoredColumnName => ['COLUMN_NAME' => $ignoredColumnName],
                $notIgnoredColumnName => ['COLUMN_NAME' => $notIgnoredColumnName]
            ]);

        $otherChangelogMock = $this->getMockForAbstractClass(ChangelogInterface::class);
        $otherChangelogMock->expects($this->any())
            ->method('getViewId')
            ->willReturn($viewId);

        $otherChangelogMock->expects($this->once())
            ->method('getColumnName')
            ->willReturn('entity_id');

        $this->viewMock->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn(
                [
                    $this->tableName => ['name' => $this->tableName, 'column' => 'columnName'],
                    'cataloginventory_stock_item' => ['name' => 'otherTableName', 'column' => 'columnName']
                ]
            );
        $this->viewMock->expects($this->atLeastOnce())
            ->method('getChangeLog')
            ->willReturn($otherChangelogMock);

        $model = new Subscription(
            $this->resourceMock,
            $this->triggerFactoryMock,
            $this->viewCollectionMock,
            $this->viewMock,
            $tableName,
            'columnName',
            [],
            $ignoredData,
            $mviewConfigMock,
            $statementPostprocessorMock
        );

        $method = new ReflectionMethod($model, 'buildStatement');
        $method->setAccessible(true);
        $statement = $method->invoke($model, Trigger::EVENT_UPDATE, $this->viewMock);

        $this->assertStringNotContainsString($ignoredColumnName, $statement);
        $this->assertStringContainsString($notIgnoredColumnName, $statement);
    }
}

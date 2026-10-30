<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Lock\LockManagerInterface;
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
     * @var LockManagerInterface|MockObject
     */
    private $lockManagerMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    private $originalObjectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        // The ObjectManager is a static singleton; save and restore whatever was there
        // before this test, since nothing resets it automatically between tests.
        try {
            $this->originalObjectManager = ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $this->originalObjectManager = null;
        }

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
        $objectManagerMock = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with(DefaultProcessor::class)
            ->willReturn($this->defaultProcessor);
        ObjectManager::setInstance($objectManagerMock);
        $this->triggerFactoryMock = $this->createMock(TriggerFactory::class);
        $this->viewCollectionMock = $this->createMock(CollectionInterface::class);
        $this->viewMock = $this->createMock(ViewInterface::class);
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
        $this->lockManagerMock = $this->createMock(LockManagerInterface::class);
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
            $statementPostprocessorMock,
            $this->lockManagerMock
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->originalObjectManager) {
            ObjectManager::setInstance($this->originalObjectManager);
        }
        parent::tearDown();
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
            ->willReturnCallback(
                function ($arg1) use ($triggerMock) {
                    if ($arg1 == "INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);") {
                        return $triggerMock;
                    } elseif ($arg1 == "INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);") {
                        return $triggerMock;
                    } elseif ($arg1 == "INSERT IGNORE INTO test_view_cl (entity_id) VALUES (OLD.columnName);") {
                        return $triggerMock;
                    }
                }
            );

        $changelogMock = $this->createMock(ChangelogInterface::class);
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

        $otherChangelogMock = $this->createMock(ChangelogInterface::class);
        $otherChangelogMock->expects($this->any())
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->createMock(ViewInterface::class);
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

        $this->lockManagerMock->expects($this->exactly(3))
            ->method('lock')
            ->with('triggerName')
            ->willReturn(true);
        $this->lockManagerMock->expects($this->exactly(3))
            ->method('unlock')
            ->with('triggerName');

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

        $otherChangelogMock = $this->createMock(ChangelogInterface::class);
        $otherChangelogMock->expects($this->any())
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->createMock(ViewInterface::class);
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

        $this->lockManagerMock->expects($this->exactly(3))
            ->method('lock')
            ->with('triggerName')
            ->willReturn(true);
        $this->lockManagerMock->expects($this->exactly(3))
            ->method('unlock')
            ->with('triggerName');

        $this->model->remove();
    }

    /**
     * saveTrigger() must serialize the drop/create pair behind a lock keyed by
     * the trigger name, since the same trigger can be shared by multiple
     * subscribed indexers and rebuilt concurrently by separate processes.
     *
     * @return void
     */
    public function testSaveTriggerLocksAroundDropAndCreate(): void
    {
        $triggerMock = $this->createMock(Trigger::class);
        $triggerMock->expects($this->any())
            ->method('getName')
            ->willReturn('triggerName');

        $callOrder = [];
        $this->lockManagerMock->expects($this->once())
            ->method('lock')
            ->with('triggerName')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'lock';
                return true;
            });
        $this->connectionMock->expects($this->once())
            ->method('dropTrigger')
            ->with('triggerName')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'dropTrigger';
            });
        $this->connectionMock->expects($this->once())
            ->method('createTrigger')
            ->with($triggerMock)
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'createTrigger';
            });
        $this->lockManagerMock->expects($this->once())
            ->method('unlock')
            ->with('triggerName')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'unlock';
                return true;
            });

        $this->model->saveTrigger($triggerMock);

        $this->assertSame(['lock', 'dropTrigger', 'createTrigger', 'unlock'], $callOrder);
    }

    /**
     * If the lock could not be acquired, saveTrigger() must not attempt to
     * release a lock it never held.
     *
     * @return void
     */
    public function testSaveTriggerDoesNotUnlockWhenLockNotAcquired(): void
    {
        $triggerMock = $this->createMock(Trigger::class);
        $triggerMock->expects($this->any())
            ->method('getName')
            ->willReturn('triggerName');

        $this->lockManagerMock->expects($this->once())
            ->method('lock')
            ->with('triggerName')
            ->willReturn(false);
        $this->lockManagerMock->expects($this->never())
            ->method('unlock');
        $this->connectionMock->expects($this->once())
            ->method('dropTrigger')
            ->with('triggerName');
        $this->connectionMock->expects($this->once())
            ->method('createTrigger')
            ->with($triggerMock);

        $this->model->saveTrigger($triggerMock);
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

        $otherChangelogMock = $this->createMock(ChangelogInterface::class);
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
            $statementPostprocessorMock,
            $this->lockManagerMock
        );

        $method = new ReflectionMethod($model, 'buildStatement');
        $statement = $method->invoke($model, Trigger::EVENT_UPDATE, $this->viewMock);

        $this->assertStringNotContainsString($ignoredColumnName, $statement);
        $this->assertStringContainsString($notIgnoredColumnName, $statement);
    }
}

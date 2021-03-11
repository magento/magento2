<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit\View;

use \Magento\Framework\Mview\View\Subscription;

class SubscriptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Mysql PDO DB adapter mock
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /** @var \Magento\Framework\Mview\View\Subscription */
    protected $model;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ResourceConnection */
    protected $resourceMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Ddl\TriggerFactory */
    protected $triggerFactoryMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Mview\View\CollectionInterface */
    protected $viewCollectionMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Mview\ViewInterface */
    protected $viewMock;

    /** @var  string */
    private $tableName;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->triggerFactoryMock = $this->createMock(\Magento\Framework\DB\Ddl\TriggerFactory::class);
        $this->viewCollectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\CollectionInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->viewMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->resourceMock->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $this->model = new Subscription(
            $this->resourceMock,
            $this->triggerFactoryMock,
            $this->viewCollectionMock,
            $this->viewMock,
            $this->tableName,
            'columnName'
        );
    }

    public function testGetView()
    {
        $this->assertEquals($this->viewMock, $this->model->getView());
    }

    public function testGetTableName()
    {
        $this->assertEquals($this->tableName, $this->model->getTableName());
    }

    public function testGetColumnName()
    {
        $this->assertEquals('columnName', $this->model->getColumnName());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate()
    {
        $triggerName = 'trigger_name';
        $this->resourceMock->expects($this->atLeastOnce())->method('getTriggerName')->willReturn($triggerName);
        $triggerMock = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Trigger::class)
            ->setMethods(['setName', 'getName', 'setTime', 'setEvent', 'setTable', 'addStatement'])
            ->disableOriginalConstructor()
            ->getMock();
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->with($triggerName)
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('triggerName');
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)
            ->willReturnSelf();

        $triggerMock->expects($this->at(4))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->willReturnSelf();

        $triggerMock->expects($this->at(5))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->willReturnSelf();

        $triggerMock->expects($this->at(11))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->willReturnSelf();

        $triggerMock->expects($this->at(12))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->willReturnSelf();

        $triggerMock->expects($this->at(18))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (OLD.columnName);")
            ->willReturnSelf();

        $triggerMock->expects($this->at(19))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (OLD.columnName);")
            ->willReturnSelf();

        $changelogMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $changelogMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('test_view_cl');
        $changelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $this->viewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->willReturn($changelogMock);

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($triggerMock);

        $otherChangelogMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ViewInterface::class,
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
        $otherViewMock->expects($this->exactly(1))
            ->method('getSubscriptions')
            ->willReturn([['name' => $this->tableName], ['name' => 'otherTableName']]);
        $otherViewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->willReturn($otherChangelogMock);

        $this->viewMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn('this_id');
        $this->viewMock->expects($this->never())
            ->method('getSubscriptions');

        $this->viewCollectionMock->expects($this->exactly(1))
            ->method('getViewsByStateMode')
            ->with(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED)
            ->willReturn([$this->viewMock, $otherViewMock]);

        $this->connectionMock->expects($this->exactly(3))
            ->method('dropTrigger')
            ->with('triggerName')
            ->willReturn(true);
        $this->connectionMock->expects($this->exactly(3))
            ->method('createTrigger')
            ->with($triggerMock);

        $this->model->create();
    }

    public function testRemove()
    {
        $triggerMock = $this->createMock(\Magento\Framework\DB\Ddl\Trigger::class);
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('triggerName');
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)
            ->willReturnSelf();
        $triggerMock->expects($this->exactly(3))
            ->method('addStatement')
            ->willReturnSelf();

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($triggerMock);

        $otherChangelogMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->willReturn('other_test_view_cl');
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->willReturn('entity_id');

        $otherViewMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ViewInterface::class,
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
        $otherViewMock->expects($this->exactly(1))
            ->method('getSubscriptions')
            ->willReturn([['name' => $this->tableName], ['name' => 'otherTableName']]);
        $otherViewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->willReturn($otherChangelogMock);

        $this->viewMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn('this_id');
        $this->viewMock->expects($this->never())
            ->method('getSubscriptions');

        $this->viewCollectionMock->expects($this->exactly(1))
            ->method('getViewsByStateMode')
            ->with(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED)
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
}

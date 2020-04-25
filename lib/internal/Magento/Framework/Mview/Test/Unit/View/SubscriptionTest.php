<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\CollectionInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\ViewInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * Mysql PDO DB adapter mock
     *
     * @var MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /** @var Subscription */
    protected $model;

    /** @var MockObject|ResourceConnection */
    protected $resourceMock;

    /** @var MockObject|TriggerFactory */
    protected $triggerFactoryMock;

    /** @var MockObject|CollectionInterface */
    protected $viewCollectionMock;

    /** @var MockObject|ViewInterface */
    protected $viewMock;

    /** @var  string */
    private $tableName;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

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

        $this->resourceMock->expects($this->any())
            ->method('getTableName')
            ->will($this->returnArgument(0));

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
        $triggerMock = $this->getMockBuilder(Trigger::class)
            ->setMethods(['setName', 'getName', 'setTime', 'setEvent', 'setTable', 'addStatement'])
            ->disableOriginalConstructor()
            ->getMock();
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->with($triggerName)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('triggerName'));
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(Trigger::TIME_AFTER)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(4))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(5))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(11))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(12))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (NEW.columnName);")
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(18))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO test_view_cl (entity_id) VALUES (OLD.columnName);")
            ->will($this->returnSelf());

        $triggerMock->expects($this->at(19))
            ->method('addStatement')
            ->with("INSERT IGNORE INTO other_test_view_cl (entity_id) VALUES (OLD.columnName);")
            ->will($this->returnSelf());

        $changelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $changelogMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('test_view_cl'));
        $changelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->will($this->returnValue('entity_id'));

        $this->viewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->will($this->returnValue($changelogMock));

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->returnValue($triggerMock));

        $otherChangelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('other_test_view_cl'));
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->will($this->returnValue('entity_id'));

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
            ->will($this->returnValue('other_id'));
        $otherViewMock->expects($this->exactly(1))
            ->method('getSubscriptions')
            ->will($this->returnValue([['name' => $this->tableName], ['name' => 'otherTableName']]));
        $otherViewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->will($this->returnValue($otherChangelogMock));

        $this->viewMock->expects($this->exactly(3))
            ->method('getId')
            ->will($this->returnValue('this_id'));
        $this->viewMock->expects($this->never())
            ->method('getSubscriptions');

        $this->viewCollectionMock->expects($this->exactly(1))
            ->method('getViewsByStateMode')
            ->with(StateInterface::MODE_ENABLED)
            ->will($this->returnValue([$this->viewMock, $otherViewMock]));

        $this->connectionMock->expects($this->exactly(3))
            ->method('dropTrigger')
            ->with('triggerName')
            ->will($this->returnValue(true));
        $this->connectionMock->expects($this->exactly(3))
            ->method('createTrigger')
            ->with($triggerMock);

        $this->model->create();
    }

    public function testRemove()
    {
        $triggerMock = $this->createMock(Trigger::class);
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('triggerName'));
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(Trigger::TIME_AFTER)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('addStatement')
            ->will($this->returnSelf());

        $this->triggerFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->returnValue($triggerMock));

        $otherChangelogMock = $this->getMockForAbstractClass(
            ChangelogInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('other_test_view_cl'));
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->will($this->returnValue('entity_id'));

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
            ->will($this->returnValue('other_id'));
        $otherViewMock->expects($this->exactly(1))
            ->method('getSubscriptions')
            ->will($this->returnValue([['name' => $this->tableName], ['name' => 'otherTableName']]));
        $otherViewMock->expects($this->exactly(3))
            ->method('getChangelog')
            ->will($this->returnValue($otherChangelogMock));

        $this->viewMock->expects($this->exactly(3))
            ->method('getId')
            ->will($this->returnValue('this_id'));
        $this->viewMock->expects($this->never())
            ->method('getSubscriptions');

        $this->viewCollectionMock->expects($this->exactly(1))
            ->method('getViewsByStateMode')
            ->with(StateInterface::MODE_ENABLED)
            ->will($this->returnValue([$this->viewMock, $otherViewMock]));

        $this->connectionMock->expects($this->exactly(3))
            ->method('dropTrigger')
            ->with('triggerName')
            ->will($this->returnValue(true));

        $triggerMock->expects($this->exactly(3))
            ->method('getStatements')
            ->will($this->returnValue(true));

        $this->connectionMock->expects($this->exactly(3))
            ->method('createTrigger')
            ->with($triggerMock);

        $this->model->remove();
    }
}

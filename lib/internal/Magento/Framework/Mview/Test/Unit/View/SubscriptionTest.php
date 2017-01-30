<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Mview\Test\Unit\View;

use \Magento\Framework\Mview\View\Subscription;

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mysql PDO DB adapter mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /** @var \Magento\Framework\Mview\View\Subscription */
    protected $model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection */
    protected $resourceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Ddl\TriggerFactory */
    protected $triggerFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Mview\View\CollectionInterface */
    protected $viewCollectionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Mview\ViewInterface */
    protected $viewMock;

    /** @var  string */
    private $tableName;

    protected function setUp()
    {
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $this->resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false, false);

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will($this->returnArgument(0));

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->triggerFactoryMock = $this->getMock(
            'Magento\Framework\DB\Ddl\TriggerFactory', [], [], '', false, false
        );
        $this->viewCollectionMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\View\CollectionInterface', [], '', false, false, true, []
        );
        $this->viewMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\ViewInterface', [], '', false, false, true, []
        );

        $this->resourceMock->expects($this->any())
            ->method('getTableName')
            ->willReturn($this->tableName);

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

    public function testCreate()
    {
        $triggerName = 'trigger_name';
        $this->resourceMock->expects($this->atLeastOnce())->method('getTriggerName')->willReturn($triggerName);
        $triggerMock = $this->getMockBuilder('Magento\Framework\DB\Ddl\Trigger')
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
            ->with(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setEvent')
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('setTable')
            ->with($this->tableName)
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(6))
            ->method('addStatement')
            ->will($this->returnSelf());

        $changelogMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\View\ChangelogInterface', [], '', false, false, true, []
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
            'Magento\Framework\Mview\View\ChangelogInterface', [], '', false, false, true, []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('other_test_view_cl'));
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->will($this->returnValue('entity_id'));

        $otherViewMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\ViewInterface', [], '', false, false, true, []
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
            ->with(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED)
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
        $triggerMock = $this->getMock('Magento\Framework\DB\Ddl\Trigger', [], [], '', false, false);
        $triggerMock->expects($this->exactly(3))
            ->method('setName')
            ->will($this->returnSelf());
        $triggerMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('triggerName'));
        $triggerMock->expects($this->exactly(3))
            ->method('setTime')
            ->with(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
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
            'Magento\Framework\Mview\View\ChangelogInterface', [], '', false, false, true, []
        );
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getName')
            ->will($this->returnValue('other_test_view_cl'));
        $otherChangelogMock->expects($this->exactly(3))
            ->method('getColumnName')
            ->will($this->returnValue('entity_id'));

        $otherViewMock = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\ViewInterface', [], '', false, false, true, []
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
            ->with(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED)
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

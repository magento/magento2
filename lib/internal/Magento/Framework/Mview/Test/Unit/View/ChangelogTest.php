<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit\View;

class ChangelogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\View\Changelog
     */
    protected $model;

    /**
     * Mysql PDO DB adapter mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    protected $resourceMock;

    protected function setUp()
    {
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->resourceMock = $this->getMock(
            'Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false,
            false
        );
        $this->mockGetConnection($this->connectionMock);

        $this->model = new \Magento\Framework\Mview\View\Changelog($this->resourceMock);
    }

    public function testInstanceOf()
    {
        $resourceMock =
            $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false, false);
        $resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue(true));
        $model = new \Magento\Framework\Mview\View\Changelog($resourceMock);
        $this->assertInstanceOf('\Magento\Framework\Mview\View\ChangelogInterface', $model);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Write DB connection is not available
     */
    public function testCheckConnectionException()
    {
        $resourceMock =
            $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false, false);
        $resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue(null));
        $model = new \Magento\Framework\Mview\View\Changelog($resourceMock);
        $model->setViewId('ViewIdTest');
        $this->assertNull($model);
    }

    public function testGetName()
    {
        $this->model->setViewId('ViewIdTest');
        $this->assertEquals(
            'ViewIdTest' . '_' . \Magento\Framework\Mview\View\Changelog::NAME_SUFFIX,
            $this->model->getName()
        );
    }

    public function testGetViewId()
    {
        $this->model->setViewId('ViewIdTest');
        $this->assertEquals('ViewIdTest', $this->model->getViewId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage View's identifier is not set
     */
    public function testGetNameWithException()
    {
        $this->model->getName();
    }

    public function testGetColumnName()
    {
        $this->assertEquals(\Magento\Framework\Mview\View\Changelog::COLUMN_NAME, $this->model->getColumnName());
    }

    public function testGetVersion()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue(['Auto_increment' => 11]));

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(10, $this->model->getVersion());
    }

    public function testGetVersionWithExceptionNoAutoincrement()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue([]));

        $this->setExpectedException(
            'Exception',
            "Table status for `{$changelogTableName}` is incorrect. Can`t fetch version id."
        );
        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testGetVersionWithExceptionNoTable()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->setExpectedException('Exception', "Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testDrop()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->setExpectedException('Exception', "Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->drop();
    }

    public function testDropWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->once())
            ->method('dropTable')
            ->with($changelogTableName)
            ->will($this->returnValue(true));

        $this->model->setViewId('viewIdtest');
        $this->model->drop();
    }

    public function testCreate()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $tableMock = $this->getMock('Magento\Framework\DB\Ddl\Table', [], [], '', false, false);
        $tableMock->expects($this->exactly(2))
            ->method('addColumn')
            ->will($this->returnSelf());

        $this->connectionMock->expects($this->once())
            ->method('newTable')
            ->with($changelogTableName)
            ->will($this->returnValue($tableMock));
        $this->connectionMock->expects($this->once())
            ->method('createTable')
            ->with($tableMock);

        $this->model->setViewId('viewIdtest');
        $this->model->create();
    }

    public function testCreateWithExistingTable()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $this->connectionMock->expects($this->never())->method('createTable');
        $this->model->setViewId('viewIdtest');
        $this->model->create();
    }

    public function testGetList()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false, false);
        $selectMock->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->will($this->returnSelf());
        $selectMock->expects($this->once())
            ->method('from')
            ->with($changelogTableName, ['entity_id'])
            ->will($this->returnSelf());
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->will($this->returnSelf());

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock)
            ->will($this->returnValue(['some_data']));

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(['some_data'], $this->model->getList(1, 2));
    }

    public function testGetListWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->setExpectedException('Exception', "Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getList(random_int(1, 200), random_int(201, 400));
    }

    public function testClearWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->setExpectedException('Exception', "Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->clear(random_int(1, 200));
    }

    /**
     * @param $connection
     */
    protected function mockGetConnection($connection)
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->will($this->returnValue($connection));
    }

    protected function mockGetTableName()
    {
        $this->resourceMock->expects($this->once())->method('getTableName')->will($this->returnArgument(0));
    }

    /**
     * @param $changelogTableName
     * @param $result
     */
    protected function mockIsTableExists($changelogTableName, $result)
    {
        $this->connectionMock->expects(
            $this->once()
        )->method(
            'isTableExists'
        )->with(
            $this->equalTo($changelogTableName)
        )->will(
            $this->returnValue($result)
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit\View;

/**
 * Test Coverage for Changelog View.
 *
 * @see \Magento\Framework\Mview\View\Changelog
 */
class ChangelogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mview\View\Changelog
     */
    protected $model;

    /**
     * Mysql PDO DB adapter mock
     *
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $connectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\ResourceConnection
     */
    protected $resourceMock;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->mockGetConnection($this->connectionMock);

        $this->model = new \Magento\Framework\Mview\View\Changelog($this->resourceMock);
    }

    public function testInstanceOf()
    {
        $resourceMock =
            $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resourceMock->expects($this->once())->method('getConnection')->willReturn(true);
        $model = new \Magento\Framework\Mview\View\Changelog($resourceMock);
        $this->assertInstanceOf(\Magento\Framework\Mview\View\ChangelogInterface::class, $model);
    }

    /**
     */
    public function testCheckConnectionException()
    {
        $this->expectException(\Magento\Framework\DB\Adapter\ConnectionException::class);
        $this->expectExceptionMessage('The write connection to the database isn\'t available. Please try again later.');

        $resourceMock =
            $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resourceMock->expects($this->once())->method('getConnection')->willReturn(null);
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
     */
    public function testGetNameWithException()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('View\'s identifier is not set');

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

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['version_id' => 10]);

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(10, $this->model->getVersion());
    }

    public function testGetVersionEmptyChangelog()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(false);

        $this->model->setViewId('viewIdtest');
        $this->assertEquals(0, $this->model->getVersion());
    }

    /**
     */
    public function testGetVersionWithExceptionNoAutoincrement()
    {
        $this->expectException(\Magento\Framework\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Table status for viewIdtest_cl is incorrect. Can`t fetch version id.');

        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, true);
        $this->mockGetTableName();

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['from', 'order', 'limit'])
            ->getMock();
        $selectMock->expects($this->any())->method('from')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('order')->willReturn($selectMock);
        $selectMock->expects($this->any())->method('limit')->willReturn($selectMock);

        $this->connectionMock->expects($this->any())->method('select')->willReturn($selectMock);

        $this->connectionMock->expects($this->once())
            ->method('fetchRow')
            ->willReturn(['no_version_column' => 'blabla']);

        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testGetVersionWithExceptionNoTable()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getVersion();
    }

    public function testDrop()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
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
            ->willReturn(true);

        $this->model->setViewId('viewIdtest');
        $this->model->drop();
    }

    public function testCreate()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $tableMock = $this->createMock(\Magento\Framework\DB\Ddl\Table::class);
        $tableMock->expects($this->exactly(2))
            ->method('addColumn')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('newTable')
            ->with($changelogTableName)
            ->willReturn($tableMock);
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

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('from')
            ->with($changelogTableName, ['entity_id'])
            ->willReturnSelf();
        $selectMock->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock)
            ->willReturn([1]);

        $this->model->setViewId('viewIdtest');
        $this->assertEquals([1], $this->model->getList(1, 2));
    }

    public function testGetListWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->getList(random_int(1, 200), random_int(201, 400));
    }

    public function testClearWithException()
    {
        $changelogTableName = 'viewIdtest_cl';
        $this->mockIsTableExists($changelogTableName, false);
        $this->mockGetTableName();

        $this->expectException('Exception');
        $this->expectExceptionMessage("Table {$changelogTableName} does not exist");
        $this->model->setViewId('viewIdtest');
        $this->model->clear(random_int(1, 200));
    }

    /**
     * @param $connection
     */
    protected function mockGetConnection($connection)
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($connection);
    }

    protected function mockGetTableName()
    {
        $this->resourceMock->expects($this->once())->method('getTableName')->willReturnArgument(0);
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
        )->willReturn(
            $result
        );
    }
}

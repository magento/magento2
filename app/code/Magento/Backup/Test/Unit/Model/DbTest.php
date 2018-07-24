<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Test\Unit\Model;

use Magento\Backup\Model\Db;
use Magento\Backup\Model\ResourceModel\Db as DbResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Backup\Db\BackupInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DbTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Db
     */
    private $dbModel;

    /**
     * @var DbResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dbResourceMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionResourceMock;

    protected function setUp()
    {
        $this->dbResourceMock = $this->getMockBuilder(DbResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionResourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->dbModel = $this->objectManager->getObject(
            Db::class,
            [
                'resourceDb' => $this->dbResourceMock,
                'resource' => $this->connectionResourceMock
            ]
        );
    }

    public function testGetResource()
    {
        self::assertEquals($this->dbResourceMock, $this->dbModel->getResource());
    }

    public function testGetTables()
    {
        $tables = [];
        $this->dbResourceMock->expects($this->once())
            ->method('getTables')
            ->willReturn($tables);

        self::assertEquals($tables, $this->dbModel->getTables());
    }

    public function testGetTableCreateScript()
    {
        $tableName = 'some_table';
        $script = 'script';
        $this->dbResourceMock->expects($this->once())
            ->method('getTableCreateScript')
            ->with($tableName, false)
            ->willReturn($script);

        self::assertEquals($script, $this->dbModel->getTableCreateScript($tableName, false));
    }

    public function testGetTableDataDump()
    {
        $tableName = 'some_table';
        $dump = 'dump';
        $this->dbResourceMock->expects($this->once())
            ->method('getTableDataDump')
            ->with($tableName)
            ->willReturn($dump);

        self::assertEquals($dump, $this->dbModel->getTableDataDump($tableName));
    }

    public function testGetHeader()
    {
        $header = 'header';
        $this->dbResourceMock->expects($this->once())
            ->method('getHeader')
            ->willReturn($header);

        self::assertEquals($header, $this->dbModel->getHeader());
    }

    public function testGetFooter()
    {
        $footer = 'footer';
        $this->dbResourceMock->expects($this->once())
            ->method('getFooter')
            ->willReturn($footer);

        self::assertEquals($footer, $this->dbModel->getFooter());
    }

    public function testRenderSql()
    {
        $header = 'header';
        $script = 'script';
        $tableName = 'some_table';
        $tables = [$tableName, $tableName];
        $dump = 'dump';
        $footer = 'footer';

        $this->dbResourceMock->expects($this->once())
            ->method('getTables')
            ->willReturn($tables);
        $this->dbResourceMock->expects($this->once())
            ->method('getHeader')
            ->willReturn($header);
        $this->dbResourceMock->expects($this->exactly(2))
            ->method('getTableCreateScript')
            ->with($tableName, true)
            ->willReturn($script);
        $this->dbResourceMock->expects($this->exactly(2))
            ->method('getTableDataDump')
            ->with($tableName)
            ->willReturn($dump);
        $this->dbResourceMock->expects($this->once())
            ->method('getFooter')
            ->willReturn($footer);

        self::assertEquals(
            $header . $script . $dump . $script . $dump . $footer,
            $this->dbModel->renderSql()
        );
    }

    public function testCreateBackup()
    {
        /** @var BackupInterface|\PHPUnit_Framework_MockObject_MockObject $backupMock */
        $backupMock = $this->getMockBuilder(BackupInterface::class)->getMock();
        /** @var DataObject $tableStatus */
        $tableStatus = new DataObject();

        $tableName = 'some_table';
        $tables = [$tableName];
        $header = 'header';
        $footer = 'footer';
        $dropSql = 'drop_sql';
        $createSql = 'create_sql';
        $beforeSql = 'before_sql';
        $afterSql = 'after_sql';
        $dataSql = 'data_sql';
        $foreignKeysSql = 'foreign_keys';
        $triggersSql = 'triggers_sql';
        $rowsCount = 2;
        $dataLength = 1;

        $this->dbResourceMock->expects($this->once())
            ->method('beginTransaction');
        $this->dbResourceMock->expects($this->once())
            ->method('commitTransaction');
        $this->dbResourceMock->expects($this->once())
            ->method('getTables')
            ->willReturn($tables);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableDropSql')
            ->willReturn($dropSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableCreateSql')
            ->with($tableName, false)
            ->willReturn($createSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableDataBeforeSql')
            ->with($tableName)
            ->willReturn($beforeSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableDataAfterSql')
            ->with($tableName)
            ->willReturn($afterSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableDataSql')
            ->with($tableName, $rowsCount, 0)
            ->willReturn($dataSql);
         $this->dbResourceMock->expects($this->once())
             ->method('getTableStatus')
             ->with($tableName)
             ->willReturn($tableStatus);
        $this->dbResourceMock->expects($this->once())
            ->method('getTables')
            ->willReturn($createSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getHeader')
            ->willReturn($header);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableHeader')
            ->willReturn($header);
        $this->dbResourceMock->expects($this->once())
            ->method('getFooter')
            ->willReturn($footer);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableForeignKeysSql')
            ->willReturn($foreignKeysSql);
        $this->dbResourceMock->expects($this->once())
            ->method('getTableTriggersSql')
            ->willReturn($triggersSql);
        $backupMock->expects($this->once())
            ->method('open');
        $backupMock->expects($this->once())
            ->method('close');

        $tableStatus->setRows($rowsCount);
        $tableStatus->setDataLength($dataLength);

        $backupMock->expects($this->any())
            ->method('write')
            ->withConsecutive(
                [$this->equalTo($header)],
                [$this->equalTo($header . $dropSql . "\n")],
                [$this->equalTo($createSql . "\n")],
                [$this->equalTo($beforeSql)],
                [$this->equalTo($dataSql)],
                [$this->equalTo($afterSql)],
                [$this->equalTo($foreignKeysSql)],
                [$this->equalTo($triggersSql)],
                [$this->equalTo($footer)]
            );

        $this->dbModel->createBackup($backupMock);
    }
}

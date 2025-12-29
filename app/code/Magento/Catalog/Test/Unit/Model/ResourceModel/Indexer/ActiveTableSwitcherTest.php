<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Db_Statement_Interface;

/**
 * Unit test for \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher class.
 */
class ActiveTableSwitcherTest extends TestCase
{
    /**
     * @var ActiveTableSwitcher
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new ActiveTableSwitcher();
    }

    /**
     * @return void
     */
    public function testSwitch()
    {
        /** @var AdapterInterface|MockObject $connectionMock */
        $connectionMock = $this->createPartialMock(
            Mysql::class,
            ['changeTableComment', 'showTableStatus', 'renameTablesBatch']
        );
        $statement = $this->createMock(Zend_Db_Statement_Interface::class);
        $tableName = 'tableName';
        $tableData = ['Comment' => 'Table comment'];
        $replicaName = 'tableName_replica';
        $replicaData = ['Comment' => 'Table comment replica'];

        $connectionMock->expects($this->exactly(2))
            ->method('showTableStatus')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$tableName] => $tableData,
                [$replicaName] => $replicaData
            });

        $connectionMock->expects($this->exactly(2))
            ->method('changeTableComment')
            ->willReturnCallback(function ($arg1, $arg2)
 use ($tableName, $replicaData, $statement, $replicaName, $tableData) {
                if ($arg1 == $tableName && $arg2 == $replicaData['Comment']) {
                    return $statement;
                } elseif ($arg1 == $replicaName && $arg2 == $tableData['Comment']) {
                    return $statement;
                }
            });

        $connectionMock->expects($this->once())
            ->method('renameTablesBatch')
            ->with(
                [
                    [
                        'oldName' => 'tableName',
                        'newName' => 'tableName_outdated'
                    ],
                    [
                        'oldName' => 'tableName_replica',
                        'newName' => 'tableName'
                    ],
                    [
                        'oldName' => 'tableName_outdated',
                        'newName' => 'tableName_replica'
                    ],
                ]
            )
            ->willReturn(true);

        $this->model->switchTable($connectionMock, [$tableName]);
    }

    /**
     * @return void
     */
    public function testGetAdditionalTableName()
    {
        $tableName = 'table_name';
        $this->assertEquals(
            $tableName . '_replica',
            $this->model->getAdditionalTableName($tableName)
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TemporaryStorageTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $adapter;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var TemporaryStorage
     */
    private $model;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $config;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->tableName = 'some_table_name';

        $this->adapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $resource->expects($this->any())
            ->method('getTableName')
            ->willReturn($this->tableName);

        $this->config = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            TemporaryStorage::class,
            ['resource' => $resource, 'config' => $this->config]
        );
    }

    /**
     * @return void
     */
    public function testStoreDocumentsFromSelect(): void
    {
        $sql = 'some SQL query';

        /** @var Select|MockObject $select */
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $table = $this->createTemporaryTable();

        $table->expects($this->any())
            ->method('getName')
            ->willReturn($this->tableName);

        $this->adapter->expects($this->once())
            ->method('insertFromSelect')
            ->with($select, $this->tableName)
            ->willReturn($sql);
        $this->adapter->expects($this->once())
            ->method('query')
            ->with($sql);

        $result = $this->model->storeDocumentsFromSelect($select);

        $this->assertEquals($table, $result);
    }

    /**
     * @return void
     */
    public function testStoreDocuments(): void
    {
        $documentId = 312432;
        $documentValue = 1.235123;

        $attributeValue = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeValue->expects($this->once())
            ->method('getValue')
            ->willReturn($documentValue);

        $document = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->once())
            ->method('getId')
            ->willReturn($documentId);
        $document->expects($this->once())
            ->method('getCustomAttribute')
            ->with('score')
            ->willReturn($attributeValue);

        $table = $this->createTemporaryTable();

        $result = $this->model->storeDocuments([$document]);

        $this->assertEquals($result, $table);
    }

    /**
     * @return void
     */
    public function testStoreApiDocuments(): void
    {
        $documentId = 312432;
        $documentValue = 1.235123;

        $attributeValue = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeValue->expects($this->once())
            ->method('getValue')
            ->willReturn($documentValue);

        $document = $this->getMockBuilder(Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->once())
            ->method('getId')
            ->willReturn($documentId);
        $document->expects($this->once())
            ->method('getCustomAttribute')
            ->with('score')
            ->willReturn($attributeValue);

        $table = $this->createTemporaryTable();

        $result = $this->model->storeApiDocuments([$document]);

        $this->assertEquals($result, $table);
    }

    /**
     * @return void
     */
    public function testNoDropIfNotPersistent(): void
    {
        $this->createTemporaryTable(false);

        $this->adapter->expects($this->never())
            ->method('dropTemporaryTable');

        // model->createTemporaryTable() is a private method; this will call it
        $this->model->storeApiDocuments([]);
    }

    /**
     * @return Table|MockObject
     */
    private function createTemporaryTable($persistentConnection = true): MockObject
    {
        $this->config->expects($this->any())
            ->method('get')
            ->with('db/connection/indexer/persistent')
            ->willReturn($persistentConnection);

        $table = $this->getMockBuilder(Table::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($persistentConnection) {
            $this->adapter->expects($this->once())
                ->method('dropTemporaryTable');
        }
        $table
            ->method('addColumn')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3, $arg4, $arg5) {
                    if ($arg1 === TemporaryStorage::FIELD_ENTITY_ID &&
                        $arg2 === Table::TYPE_INTEGER &&
                        $arg3 === 10 &&
                        $arg4 === ['unsigned' => true, 'nullable' => false, 'primary' => true] &&
                        $arg5 === 'Entity ID') {
                        return null;
                    } elseif ($arg1 === 'score' &&
                        $arg2 === Table::TYPE_DECIMAL &&
                        $arg3 === [32, 16] &&
                        $arg4 === ['unsigned' => true, 'nullable' => true] &&
                        $arg5 === 'Score') {
                        return null;
                    }
                }
            );
        $table->expects($this->once())
            ->method('setOption')
            ->with('type', 'memory');

        $this->adapter->expects($this->once())
            ->method('newTable')
            ->with($this->tableName)
            ->willReturn($table);
        $this->adapter->expects($this->once())
            ->method('createTemporaryTable')
            ->with($table);

        return $table;
    }
}

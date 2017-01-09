<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TemporaryStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->tableName = 'some_table_name';

        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $resource->expects($this->any())
            ->method('getTableName')
            ->willReturn($this->tableName);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class,
            ['resource' => $resource]
        );
    }

    public function testStoreDocumentsFromSelect()
    {
        $sql = 'some SQL query';

        /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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

    public function testStoreDocuments()
    {
        $documentId = 312432;
        $documentValue = 1.235123;

        $attributeValue = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeValue->expects($this->once())
            ->method('getValue')
            ->willReturn($documentValue);

        $document = $this->getMockBuilder(\Magento\Framework\Api\Search\Document::class)
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

    public function testStoreApiDocuments()
    {
        $documentId = 312432;
        $documentValue = 1.235123;

        $attributeValue = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeValue->expects($this->once())
            ->method('getValue')
            ->willReturn($documentValue);

        $document = $this->getMockBuilder(\Magento\Framework\Api\Search\Document::class)
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
     * @return \Magento\Framework\DB\Ddl\Table|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTemporaryTable()
    {
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->at(1))
            ->method('addColumn')
            ->with(
                TemporaryStorage::FIELD_ENTITY_ID,
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            );
        $table->expects($this->at(2))
            ->method('addColumn')
            ->with(
                'score',
                Table::TYPE_DECIMAL,
                [32, 16],
                ['unsigned' => true, 'nullable' => false],
                'Score'
            );
        $table->expects($this->once())
            ->method('setOption')
            ->with('type', 'memory');

        $this->adapter->expects($this->once())
            ->method('newTable')
            ->with($this->tableName)
            ->willReturn($table);
        $this->adapter->expects($this->once())
            ->method('dropTemporaryTable');
        $this->adapter->expects($this->once())
            ->method('createTemporaryTable')
            ->with($table);

        return $table;
    }
}

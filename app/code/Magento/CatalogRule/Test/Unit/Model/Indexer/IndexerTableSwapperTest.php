<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexerTableSwapper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\CatalogRule\Model\Indexer\IndexerTableSwapper class.
 */
class IndexerTableSwapperTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterfaceMock;

    /**
     * @var \Zend_Db_Statement_Interface|MockObject
     */
    private $statementInterfaceMock;

    /**
     * @var Table|MockObject
     */
    private $tableMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);

        $this->adapterInterfaceMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var \Zend_Db_Statement_Interface $statementInterfaceMock */
        $this->statementInterfaceMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->getMockForAbstractClass();
        /** @var Table $tableMock */
        $this->tableMock = $this->createMock(Table::class);
        $this->resourceConnectionMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapterInterfaceMock);
    }

    /**
     * @return void
     */
    public function testGetWorkingTableNameWithExistingTemporaryTable(): void
    {
        $model = new IndexerTableSwapper($this->resourceConnectionMock);
        $originalTableName = 'catalogrule_product';
        $temporaryTableNames = ['catalogrule_product' => 'catalogrule_product__temp9604'];
        $this->setObjectProperty($model, 'temporaryTables', $temporaryTableNames);

        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with($originalTableName)
            ->willReturn($originalTableName);

        $this->assertEquals(
            $temporaryTableNames[$originalTableName],
            $model->getWorkingTableName($originalTableName)
        );
    }

    /**
     * @return void
     */
    public function testGetWorkingTableNameWithoutExistingTemporaryTable(): void
    {
        $model = new IndexerTableSwapper($this->resourceConnectionMock);
        $originalTableName = 'catalogrule_product';
        $temporaryTableName = 'catalogrule_product__temp9604';
        $this->setObjectProperty($model, 'temporaryTables', []);

        $this->resourceConnectionMock->expects($this->at(0))
            ->method('getTableName')
            ->with($originalTableName)
            ->willReturn($originalTableName);
        $this->resourceConnectionMock->expects($this->at(1))
            ->method('getTableName')
            ->with($this->stringStartsWith($originalTableName . '__temp'))
            ->willReturn($temporaryTableName);

        $this->assertEquals(
            $temporaryTableName,
            $model->getWorkingTableName($originalTableName)
        );
    }

    /**
     * Sets object non-public property.
     *
     * @param mixed $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     */
    private function setObjectProperty($object, string $propertyName, $value): void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @return void
     */
    public function testSwapIndexTables(): void
    {
        $model = $this->getMockBuilder(IndexerTableSwapper::class)
            ->setMethods(['getWorkingTableName'])
            ->setConstructorArgs([$this->resourceConnectionMock])
            ->getMock();
        $originalTableName = 'catalogrule_product';
        $temporaryOriginalTableName = 'catalogrule_product9604';
        $temporaryTableName = 'catalogrule_product__temp9604';
        $toRename = [
            [
                'oldName' => $originalTableName,
                'newName' => $temporaryOriginalTableName,
            ],
            [
                'oldName' => $temporaryTableName,
                'newName' => $originalTableName,
            ],
        ];

        $this->resourceConnectionMock->expects($this->at(0))
            ->method('getTableName')
            ->with($originalTableName)
            ->willReturn($originalTableName);
        $this->resourceConnectionMock->expects($this->at(1))
            ->method('getTableName')
            ->with($this->stringStartsWith($originalTableName))
            ->willReturn($temporaryOriginalTableName);
        $model->expects($this->once())
            ->method('getWorkingTableName')
            ->with($originalTableName)
            ->willReturn($temporaryTableName);
        $this->adapterInterfaceMock->expects($this->once())
            ->method('renameTablesBatch')
            ->with($toRename)
            ->willReturn(true);
        $this->adapterInterfaceMock->expects($this->once())
            ->method('dropTable')
            ->with($temporaryOriginalTableName)
            ->willReturn(true);

        $model->swapIndexTables([$originalTableName]);
    }
}

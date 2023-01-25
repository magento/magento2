<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /** @var Store */
    protected $model;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /** @var  Select|MockObject */
    protected $select;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->select = $this->createMock(Select::class);
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            [
                'getConnection',
                'getTableName'
            ]
        );
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isTableExists',
                'select',
                'fetchAll',
                'fetchOne',
                'from',
                'getCheckSql',
                'where',
                'quoteIdentifier',
                'quote'
            ])
            ->getMockForAbstractClass();

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $configCacheTypeMock = $this->createMock('\Magento\Framework\App\Cache\Type\Config');
        $this->model = $objectManagerHelper->getObject(
            Store::class,
            [
                'context' => $contextMock,
                'configCacheType' => $configCacheTypeMock
            ]
        );
    }

    public function testCountAll($countAdmin = false)
    {
        $mainTable = 'store';
        $tableIdentifier = 'code';
        $tableIdentifierValue = 'admin';
        $count = 1;

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->select);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($mainTable);

        $this->select->expects($this->once())
            ->method('from')
            ->with($mainTable, 'COUNT(*)')
            ->willReturnSelf();

        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->with($tableIdentifier)
            ->willReturn($tableIdentifier);

        $this->connectionMock->expects($this->once())
            ->method('quote')
            ->with($tableIdentifierValue)
            ->willReturn($tableIdentifierValue);

        $this->select->expects($this->any())
            ->method('where')
            ->with(sprintf('%s <> %s', $tableIdentifier, $tableIdentifierValue))
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->select)
            ->willReturn($count);

        $this->assertEquals($count, $this->model->countAll($countAdmin));
    }

    public function testReadAllStores()
    {
        $mainTable = 'store';
        $data = [
            ["store_id" => "0", "code" => "admin", "website_id" => 0, "name" => "Admin"],
            ["store_id" => "1", "code" => "default", "website_id" => 1, "name" => "Default Store View"]
        ];

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn($mainTable);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->select);

        $this->select->expects($this->once())
            ->method('from')
            ->with($mainTable)
            ->willReturnSelf();

        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn($data);

        $this->assertEquals($data, $this->model->readAllStores());
    }
}

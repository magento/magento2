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
use Magento\Store\Model\ResourceModel\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /** @var  Website */
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
        $this->select =  $this->createMock(Select::class);
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            [
                'getConnection',
                'getTableName'
            ]
        );
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->addMethods(['from', 'joinLeft', 'where'])
            ->onlyMethods(['isTableExists', 'select', 'fetchAll', 'fetchOne', 'getCheckSql'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->model = $objectManagerHelper->getObject(
            Website::class,
            [
                'context' => $contextMock
            ]
        );
    }

    public function testReadAllWebsites()
    {
        $data = [
            "admin" => ["website_id" => "0", "code" => "admin", "name" => "Admin"],
            "base" => ["website_id" => "1", "code" => "base", "name" => "Main Website"]
        ];
        $mainTable = 'store_website';

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($mainTable);

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

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

        $this->assertEquals($data, $this->model->readAllWebsites());
    }

    public function testGetDefaultStoresSelect($includeDefault = false)
    {
        $storeId = 1;
        $storeWebsiteTable = 'store_website';
        $storeGroupTable = 'store_group';

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('getCheckSql')
            ->with(
                'store_group_table.default_store_id IS NULL',
                '0',
                'store_group_table.default_store_id'
            )
            ->willReturn($storeId);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->select);

        $this->resourceMock->expects($this->atLeastOnce())
            ->method('getTableName')
            ->withConsecutive([$storeWebsiteTable], [$storeGroupTable])
            ->willReturnOnConsecutiveCalls($storeWebsiteTable, $storeGroupTable);

        $this->select->expects($this->once())
            ->method('from')
            ->with(
                ['website_table' => $storeWebsiteTable],
                ['website_id']
            )
            ->willReturnSelf();

        $this->select->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['store_group_table' => $storeGroupTable],
                'website_table.website_id=store_group_table.website_id' .
                ' AND website_table.default_group_id = store_group_table.group_id',
                ['store_id' => $storeId]
            )
            ->willReturnSelf();

        $this->assertInstanceOf('\Magento\Framework\DB\Select', $this->model->getDefaultStoresSelect($includeDefault));
    }

    public function testCountAll($includeDefault = false)
    {
        $count = 2;
        $mainTable = 'store_website';

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

        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->select)
            ->willReturn($count);

        $this->assertEquals($count, $this->model->countAll($includeDefault));
    }
}

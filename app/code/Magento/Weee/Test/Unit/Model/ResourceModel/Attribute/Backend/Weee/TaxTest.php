<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Model\ResourceModel\Attribute\Backend\Weee;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxTest extends TestCase
{
    /**
     * @var Tax
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $connectionMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = new Tax(
            $contextMock,
            $this->storeManagerMock
        );
    }

    public function testInsertProductData()
    {
        $productId = 100;
        $productMock = $this->createPartialMock(Product::class, ['getId']);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);

        $this->connectionMock->expects($this->once())
            ->method('insert')
            ->with('table_name', ['entity_id' => $productId]);

        $this->assertEquals($this->model, $this->model->insertProductData($productMock, []));
    }
}

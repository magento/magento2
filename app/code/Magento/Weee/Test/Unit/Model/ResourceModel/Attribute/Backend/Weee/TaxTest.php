<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\ResourceModel\Attribute\Backend\Weee;

class TaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = new \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax(
            $contextMock,
            $this->storeManagerMock
        );
    }

    public function testInsertProductData()
    {
        $productId = 100;
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId']);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);

        $this->connectionMock->expects($this->once())
            ->method('insert')
            ->with('table_name', ['entity_id' => $productId]);

        $this->assertEquals($this->model, $this->model->insertProductData($productMock, []));
    }
}

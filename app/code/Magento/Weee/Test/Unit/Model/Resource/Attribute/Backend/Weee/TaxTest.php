<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\Resource\Attribute\Backend\Weee;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\Resource\Attribute\Backend\Weee\Tax
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
    protected $adapterMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->adapterMock = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');

        $this->resourceMock = $this->getMock('\Magento\Framework\App\Resource', [], [], '', false);
        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->with('core_write')
            ->willReturn($this->adapterMock);

        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn('table_name');

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);

        $this->model = new \Magento\Weee\Model\Resource\Attribute\Backend\Weee\Tax(
            $contextMock,
            $this->storeManagerMock
        );
    }

    public function testInsertProductData()
    {
        $productId = 100;
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', ['getId'], [], '', false);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);

        $this->adapterMock->expects($this->once())
            ->method('insert')
            ->with('table_name', ['entity_id' => $productId]);

        $this->assertEquals($this->model, $this->model->insertProductData($productMock, []));
    }
}

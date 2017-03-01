<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\ResourceModel;

use Magento\AdvancedSearch\Model\ResourceModel\Index;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Indexer\Model\ResourceModel\FrontendResource;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Index
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryProductIndexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class);
        $this->resourceContextMock = $this->getMock(Context::class, [], [], '', false);
        $this->resourceConnectionMock = $this->getMock(ResourceConnection::class, [], [], '', false);
        $this->resourceContextMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceConnectionMock);
        $this->adapterMock = $this->getMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);
        $this->metadataPoolMock = $this->getMock(MetadataPool::class, [], [], '', false);
        $this->frontendResourceMock = $this->getMock(FrontendResource::class, [], [], '', false);
        $this->categoryProductIndexerMock = $this->getMock(
            FrontendResource::class,
            [],
            [],
            '',
            false
        );

        $this->model = new Index(
            $this->resourceContextMock,
            $this->storeManagerMock,
            $this->metadataPoolMock,
            null,
            $this->frontendResourceMock,
            $this->categoryProductIndexerMock
        );
    }

    public function testGetPriceIndexDataUsesFrontendPriceIndexerTable()
    {
        $storeId = 1;
        $storeMock = $this->getMock(StoreInterface::class);
        $storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($storeMock);

        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();
        $this->adapterMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->adapterMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn([]);

        // verify that frontend indexer table is used
        $this->frontendResourceMock->expects($this->once())->method('getMainTable');

        $this->assertEmpty($this->model->getPriceIndexData([1], $storeId));
    }
}

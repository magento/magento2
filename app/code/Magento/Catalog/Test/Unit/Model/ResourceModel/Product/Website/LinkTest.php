<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbSelect;

    protected function setUp()
    {
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connection =
            $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->dbSelect = $this->getMockBuilder(\Magento\Framework\Db\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->dbSelect);
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->resource->expects($this->atLeastOnce())
            ->method('getTableName')
            ->with('catalog_product_website')
            ->willReturn('catalog_product_website');
        $this->model = new Link($this->resource);
    }

    public function testGetWebsiteIdByProductId()
    {
        $websiteIds = [1,2];
        $productId = 1;
        $this->dbSelect->expects($this->once())
            ->method("from")
            ->with('catalog_product_website', 'website_id')
            ->willReturn($this->dbSelect);
        $this->dbSelect->expects($this->once())
            ->method("where")
            ->with('product_id = ?', (int) $productId);
        $this->connection->expects($this->once())
            ->method('fetchCol')
            ->willReturn($websiteIds);

        $this->assertEquals($websiteIds, $this->model->getWebsiteIdsByProductId($productId));
    }

    public function testSaveWebsiteIds()
    {
        $newWebsiteIds = [2,3];
        $websiteIds = [1,2];
        $productId = 1;
        $product = $this->createMock(ProductInterface::class);
        $product->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($productId);
        $this->dbSelect->expects($this->once())
            ->method("from")
            ->with('catalog_product_website', 'website_id')
            ->willReturn($this->dbSelect);
        $this->dbSelect->expects($this->once())
            ->method("where")
            ->with('product_id = ?', (int) $productId);
        $this->connection->expects($this->once())
            ->method('fetchCol')
            ->willReturn($websiteIds);

        $this->connection->expects($this->once())
            ->method('insertMultiple')
            ->with('catalog_product_website', [
                ['product_id' => $productId, 'website_id' => 3]
            ]);

        $this->connection->expects($this->once())
            ->method('delete')
            ->with('catalog_product_website', ['product_id = ?' => $productId, 'website_id = ?' => 1]);

        $this->assertTrue($this->model->saveWebsiteIds($product, $newWebsiteIds));
    }
}

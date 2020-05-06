<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /**
     * @var Link
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var MockObject
     */
    protected $dbSelect;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->connection =
            $this->getMockForAbstractClass(AdapterInterface::class);
        $this->dbSelect = $this->getMockBuilder(Select::class)
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
        $product = $this->getMockForAbstractClass(ProductInterface::class);
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

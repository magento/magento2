<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Model\ResourceModel\Product\StockStatusBaseSelectProcessor;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class StockStatusBaseSelectProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var StockStatusBaseSelectProcessor
     */
    private $stockStatusBaseSelectProcessor;

    protected function setUp()
    {
        $this->resource = $this->getMockBuilder(ResourceConnection::class)->disableOriginalConstructor()->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)->disableOriginalConstructor()->getMock();
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->stockStatusBaseSelectProcessor =  (new ObjectManager($this))->getObject(
            StockStatusBaseSelectProcessor::class,
            [
                'resource' => $this->resource,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    public function testProcess()
    {
        $linkField = 'link_field';
        $tableName = 'table_name';

        $metadata = $this->getMock(EntityMetadataInterface::class);
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);

        $this->resource->expects($this->once())
            ->method('getTableName')
            ->with('cataloginventory_stock_status')
            ->willReturn($tableName);

        $this->select->expects($this->once())
            ->method('join')
            ->with(
                ['stock' => $tableName],
                sprintf('stock.product_id = %s.%s', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS, $linkField),
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('where')
            ->with('stock.stock_status = ?', Stock::STOCK_IN_STOCK)
            ->willReturnSelf();

        $this->stockStatusBaseSelectProcessor->process($this->select);
    }
}

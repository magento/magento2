<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 *
 * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
 * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_out_of_stock_with_multiselect_attribute.php
 */
class StockStatusFilterWithGeneralFilterTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockStatusFilter
     */
    private $stockStatusFilter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->stockStatusFilter = $this->objectManager->get(StockStatusFilter::class);
    }

    /**
     * @return void
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type: some_wrong_type
     */
    public function testApplyWithWrongType()
    {
        $select = $this->resource->getConnection()->select();
        $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            'some_wrong_type',
            true
        );
    }

    /**
     * @param bool $showOutOfStockFlag
     * @param int $expectedResult
     * @return void
     *
     * @dataProvider applyDataProvider
     */
    public function testApply(bool $showOutOfStockFlag, int $expectedResult)
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
            [$this->resource->getTableName('catalog_product_index_eav')],
            ['entity_id']
        )->distinct(true);

        $select = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY,
            $showOutOfStockFlag
        );
        $data = $select->query()->fetchAll();

        $this->assertEquals($expectedResult, count($data));
    }

    /**
     * @return array
     */
    public function applyDataProvider(): array
    {
        return [
            [true, 6],
            [false, 4],
        ];
    }
}

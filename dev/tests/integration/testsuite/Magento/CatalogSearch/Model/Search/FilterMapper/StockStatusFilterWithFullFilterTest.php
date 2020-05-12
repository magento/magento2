<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDbIsolation disabled
 *
 * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
 * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
 * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/product_out_of_stock_with_multiselect_attribute.php
 */
class StockStatusFilterWithFullFilterTest extends TestCase
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
     * @var CustomAttributeFilter
     */
    private $customAttributeFilter;

    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->resource = $this->objectManager->get(ResourceConnection::class);
        $this->stockStatusFilter = $this->objectManager->get(StockStatusFilter::class);
        $this->customAttributeFilter = $this->objectManager->get(CustomAttributeFilter::class);
        $eavConfig = $this->objectManager->get(EavConfig::class);
        $attribute = $eavConfig->getAttribute(Product::ENTITY, 'multiselect_attribute');

        $productRepository = $this->objectManager->get(ProductRepository::class);
        $product = $productRepository->get('simple_ms_2');
        $multiSelectArray = explode(',', $product->getData('multiselect_attribute'));

        $this->filter = $this->objectManager->create(
            Term::class,
            [
                'field' => $attribute->getAttributeCode(),
                'name' => $attribute->getAttributeCode() . '_filter',
                'value' => reset($multiSelectArray),
            ]
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
        $select = $this->customAttributeFilter->apply($select, $this->filter);
        $select = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS,
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
            [true, 2],
            [false, 1],
        ];
    }
}

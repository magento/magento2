<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Class RowsTest
 */
class RowsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_processor;

    protected function setUp()
    {
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductUpdate()
    {
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\CategoryFactory'
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Block\Product\ListProduct'
        );

        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceBuilder $stockRegistry */
        $stockItemBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Api\Data\StockItemInterfaceBuilder'
        );

        /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
        $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Api\StockRegistryInterface'
        );
        /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Api\StockItemRepositoryInterface'
        );

        /** @var \Magento\CatalogInventory\Model\Resource\Stock\Item $stockItemResource */
        $stockItemResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogInventory\Model\Resource\Stock\Item'
        );

        $stockItem = $stockRegistry->getStockItem(1, 1);

        $stockItemData = [
            'qty' => $stockItem->getQty() + 12,
        ];

        $stockItemBuilder = $stockItemBuilder->mergeDataObjectWithArray($stockItem, $stockItemData);
        $stockItemSave = $stockItemBuilder->create();
        $stockItemResource->setProcessIndexEvents(false);

        $stockItemRepository->save($stockItemSave);

        $this->_processor->reindexList([1]);

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productCollection->joinField(
            'qty',
            'cataloginventory_stock_status',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->assertEquals(1, $productCollection->count());
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals('Simple Product', $product->getName());
            $this->assertEquals('Short description', $product->getShortDescription());
            $this->assertEquals(112, $product->getQty());
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Ui\DataProvider\Product\AddQuantityAndStockStatusFieldToCollection;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Quantity and stock status test
 */
class QuantityAndStockStatusTest extends TestCase
{
    /**
     * @var string
     */
    private static $quantityAndStockStatus = 'quantity_and_stock_status';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test product stock status in the products grid column
     *
     * @magentoDataFixture Magento/Catalog/_files/quantity_and_stock_status_attribute_used_in_grid.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testProductStockStatus()
    {
        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->objectManager->create(StockItemRepository::class);

        /** @var StockRegistryInterface $stockRegistry */
        $stockRegistry = $this->objectManager->create(StockRegistryInterface::class);

        $stockItem = $stockRegistry->getStockItemBySku('simple');
        $stockItem->setIsInStock(false);
        $stockItemRepository->save($stockItem);
        $savedStockStatus = (int)$stockItem->getIsInStock();

        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'addFieldStrategies' => [
                    'quantity_and_stock_status' =>
                        $this->objectManager->get(AddQuantityAndStockStatusFieldToCollection::class)
                ]
            ]
        );

        $dataProvider->addField(self::$quantityAndStockStatus);
        $data = $dataProvider->getData();
        $dataProviderStockStatus = $data['items'][0][self::$quantityAndStockStatus];

        $this->assertEquals($dataProviderStockStatus, $savedStockStatus);
    }
}

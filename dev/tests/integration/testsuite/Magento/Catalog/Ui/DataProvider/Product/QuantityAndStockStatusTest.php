<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Ui\DataProvider\Product\AddIsInStockFieldToCollection;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

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
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $productId = $product->getId();

        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->objectManager->create(StockItemRepository::class);

        $stockItem = $stockItemRepository->get($productId);
        $stockItem->setIsInStock(false);
        $stockItemRepository->save($stockItem);
        $savedStockItem = $stockItemRepository->get($productId);
        $savedStockStatus = $savedStockItem->getData('is_in_stock');

        $dataProvider = $this->objectManager->create(
            ProductDataProvider::class,
            [
                'name' => 'product_listing_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
                'addFieldStrategies' => [
                    'quantity_and_stock_status' =>
                        $this->objectManager->get(AddIsInStockFieldToCollection::class)
                ]
            ]
        );

        $dataProvider->addField(self::$quantityAndStockStatus);
        $data = $dataProvider->getData();
        $dataProviderStockStatus = $data['items'][0][self::$quantityAndStockStatus];

        $this->assertEquals($dataProviderStockStatus, $savedStockStatus);
    }
}

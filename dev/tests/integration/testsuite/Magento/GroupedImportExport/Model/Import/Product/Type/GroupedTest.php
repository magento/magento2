<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedImportExport\Model\Import\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /**
     * Configurable product test Name
     */
    const TEST_PRODUCT_NAME = 'Test Grouped';

    /**
     * Configurable product test Type
     */
    const TEST_PRODUCT_TYPE = 'grouped';

    /**
     * @var ProductImport
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Grouped product options SKU list
     *
     * @var array
     */
    private $optionSkuList = ['Simple for Grouped 1', 'Simple for Grouped 2'];

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(ProductImport::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testImport()
    {
        // Import data from CSV file
        $pathToFile = __DIR__ . '/../../_files/grouped_product.csv';
        $this->import($pathToFile);

        $resource = $this->objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku('Test Grouped');
        $this->assertIsNumeric($productId);
        $product = $this->objectManager->create(Product::class);
        $product->load($productId);

        $this->assertFalse($product->isObjectNew());
        $this->assertEquals(self::TEST_PRODUCT_NAME, $product->getName());
        $this->assertEquals(self::TEST_PRODUCT_TYPE, $product->getTypeId());

        $childProductCollection = $product->getTypeInstance()->getAssociatedProducts($product);

        foreach ($childProductCollection as $childProduct) {
            $this->assertContains($childProduct->getSku(), $this->optionSkuList);
        }
    }

    /**
     * Verify grouped product stock status updated during import.
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @return void
     */
    public function testImportUpdateStockStatus(): void
    {
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        //Verify grouped product is out of stock after import.
        $pathToOutOfStockFile = __DIR__ . '/../../_files/grouped_product_children_out_of_stock.csv';
        $this->import($pathToOutOfStockFile);
        $groupedProduct = $productRepository->get('grouped-product', false, null, true);
        $stockItem = $this->getStockItem((int)$groupedProduct->getId());
        self::assertFalse($stockItem->getIsInStock());
        //Verify grouped product is in stock after import.
        $pathToOutOfStockFile = __DIR__ . '/../../_files/grouped_product_children_in_stock.csv';
        $this->import($pathToOutOfStockFile);
        $groupedProduct = $productRepository->get('grouped-product', false, null, true);
        $stockItem = $this->getStockItem((int)$groupedProduct->getId());
        self::assertTrue($stockItem->getIsInStock());
    }

    /**
     * Retrieve product stock status.
     *
     * @param int $productId
     * @return StockItemInterface|null
     */
    private function getStockItem(int $productId): ?StockItemInterface
    {
        $criteriaFactory = $this->objectManager->create(StockItemCriteriaInterfaceFactory::class);
        $stockItemRepository = $this->objectManager->create(StockItemRepositoryInterface::class);
        $stockConfiguration = $this->objectManager->create(StockConfigurationInterface::class);
        $criteria = $criteriaFactory->create();
        $criteria->setScopeFilter($stockConfiguration->getDefaultScopeId());
        $criteria->setProductsFilter($productId);
        $items = $stockItemRepository->getList($criteria)->getItems();

        return reset($items);
    }


    /**
     * Perform products import.
     *
     * @param string $pathToFile
     * @throws LocalizedException
     */
    private function import(string $pathToFile): void
    {
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(Csv::class, ['file' => $pathToFile, 'directory' => $directory]);
        $errors = $this->model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->model->importData();
    }
}

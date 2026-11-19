<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Test\Fixture\CsvFile;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verify that product import persistence consumes transformed bunches.
 */
#[AppIsolation(true)]
#[DbIsolation(true)]
class ProductBunchTest extends TestCase
{
    public function testSaveProductsUsesTransformedBunch(): void
    {
        $this->importRows([$this->productRow('bunch-product')]);

        $product = Bootstrap::getObjectManager()->create(CatalogProduct::class);
        $product->load($product->getIdBySku('bunch-product'));
        self::assertSame('Transformed name', $product->getName());
    }

    public function testSaveStockUsesTransformedBunch(): void
    {
        $this->importRows([$this->productRow('bunch-stock')]);

        $stock = Bootstrap::getObjectManager()->get(StockRegistryInterface::class)->getStockItemBySku('bunch-stock');
        self::assertEquals(37, $stock->getQty());
    }

    public function testDeleteUsesTransformedBunch(): void
    {
        $this->importRows([$this->productRow('bunch-source'), $this->productRow('bunch-target')]);
        $this->importRows([['sku' => 'bunch-source']], Import::BEHAVIOR_DELETE);

        $product = Bootstrap::getObjectManager()->create(CatalogProduct::class);
        self::assertNotEmpty($product->getIdBySku('bunch-source'));
        self::assertFalse($product->getIdBySku('bunch-target'));
    }

    public function testGetNextBunchFiltersAndDeduplicatesIds(): void
    {
        $model = Bootstrap::getObjectManager()->create(ProductBunchTransformer::class);
        $dataSource = $model->getDataSourceModel();
        $row = $this->productRow('bunch-selected');
        $id = $dataSource->saveBunch('catalog_product', Import::BEHAVIOR_APPEND, [0 => $row]);
        $dataSource->saveBunch('catalog_product', Import::BEHAVIOR_APPEND, [$this->productRow('bunch-excluded')]);
        $model->setIds([$id, $id]);
        $row['name'] = 'Transformed name';
        $row['qty'] = 37;

        self::assertSame([0 => $row], $model->getNextBunch());
        self::assertNull($model->getNextBunch());
    }

    /**
     * Import CSV rows through validation and persistence.
     *
     * @param array $rows
     * @param string $behavior
     * @return void
     */
    private function importRows(array $rows, string $behavior = Import::BEHAVIOR_APPEND): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixture = $objectManager->create(CsvFile::class);
        $file = $fixture->apply(['rows' => $rows]);
        try {
            $source = $objectManager->create(Csv::class, [
                'file' => $file->getData('absolute_path'),
                'directory' => $objectManager->get(Filesystem::class)->getDirectoryRead(DirectoryList::ROOT),
            ]);
            $model = $objectManager->create(ProductBunchTransformer::class);
            $model->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product'])->setSource($source);
            self::assertSame(0, $model->validateData()->getErrorsCount());
            self::assertTrue($model->importData());
            self::assertSame(0, $model->getErrorAggregator()->getErrorsCount());
        } finally {
            $fixture->revert($file);
        }
    }

    /**
     * Build a valid simple product CSV row.
     *
     * @param string $sku
     * @return array
     */
    private function productRow(string $sku): array
    {
        return [
            'sku' => $sku,
            'attribute_set_code' => 'Default',
            'product_type' => 'simple',
            'name' => 'Original name',
            'url_key' => $sku,
            'price' => '10',
            'qty' => '5',
            'is_in_stock' => '1',
        ];
    }
}

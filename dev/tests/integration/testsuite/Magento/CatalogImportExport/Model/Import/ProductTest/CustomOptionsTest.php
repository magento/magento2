<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class CustomOptionsTest extends ProductTestBase
{
    /**
     * Test for custom options based on AC-3637
     *
     * @dataProvider productsWithCustomOptionsDataProvider
     * @param string $filename
     * @param string $sku
     * @param int $numOfCustomOptions
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @return void
     */
    public function testImportDifferentCustomOptions(string $filename, string $sku, int $numOfCustomOptions): void
    {
        $pathToFile = __DIR__ . '/../_files/' . $filename;
        $importModel = $this->createImportModel($pathToFile, Import::BEHAVIOR_ADD_UPDATE);
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);

        $this->assertInstanceOf(Product::class, $product);
        $options = $product->getOptionInstance()->getProductOptions($product);
        $this->assertCount($numOfCustomOptions, $options);

        try {
            $this->productRepository->delete($product);
        } catch (NoSuchEntityException $e) {
            //already deleted
        }
    }

    /**
     * @return array
     */
    public static function productsWithCustomOptionsDataProvider(): array
    {
        return [
            [
                'filename' => '001_simple1_no_custom_options.csv',
                'sku' => 'simple1',
                'numOfCustomOptions' => 0,
            ],
            [
                'filename' => '002_simple1_4_custom_options.csv',
                'sku' => 'simple1',
                'numOfCustomOptions' => 4,
            ],
            [
                'filename' => '003_simple1_5_custom_options.csv',
                'sku' => 'simple1',
                'numOfCustomOptions' => 5,
            ],
            [
                'filename' => '004_simple1_5_custom_options_1_updated.csv',
                'sku' => 'simple1',
                'numOfCustomOptions' => 5,
            ],
            [
                'filename' => '005_simple1_no_custom_options.csv',
                'sku' => 'simple1',
                'numOfCustomOptions' => 0,
            ],
        ];
    }
}

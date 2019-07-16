<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Integration\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogImportExport\Model\Export\Product as ProductExporter;
use Magento\CatalogImportExport\Model\Import\Product as ProductImporter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Export\Adapter\Csv as ExportCsv;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv as ImportCsv;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Base class for product import and export.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ProductImportExportBase extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $exportFilePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Build product exporter for test export.
     *
     * @return ProductExporter
     */
    public function getProductExporter(): ProductExporter
    {
        $sandboxDir = Bootstrap::getInstance()->getBootstrap()->getApplication()->getTempDir();
        $this->exportFilePath = implode(
            DIRECTORY_SEPARATOR,
            [
                $sandboxDir,
                'var',
                uniqid('test-export_', false) . '.csv'
            ]
        );
        $writer = $this->objectManager->create(ExportCsv::class, ['destination' => $this->exportFilePath]);
        $productExporter = $this->objectManager->get(ProductExporter::class);
        $productExporter->setWriter($writer);
        $productExporter->setParameters([]);

        return $productExporter;
    }

    /**
     * Build product importer for import test.
     *
     * @return ProductImporter
     */
    public function getProductImporter(): ProductImporter
    {
        $productImporter = $this->objectManager->get(ProductImporter::class);
        $this->filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = Bootstrap::getObjectManager()->create(
            ImportCsv::class,
            [
                'file' => $this->exportFilePath,
                'directory' => $directory
            ]
        );
        $productImporter->setParameters(
            [
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'entity' => ProductModel::ENTITY
            ]
        );
        $productImporter->setSource($source);

        return $productImporter;
    }

    /**
     * Verify, deleted products and imported are the same.
     *
     * @param array $deletedProducts
     * @param array $importedProducts
     * @return void
     */
    public function verifyProducts(array $deletedProducts, array $importedProducts): void
    {
        $this->assertEquals(count($deletedProducts), count($importedProducts));
        foreach ($importedProducts as $importedProduct) {
            foreach ($deletedProducts as $existedProduct) {
                if ($importedProduct->getSku() === $existedProduct->getSku()) {
                    $this->assertEquals($existedProduct->getName(), $importedProduct->getName());
                    $this->assertEquals($existedProduct->getPrice(), $importedProduct->getPrice());
                    $this->assertEquals(
                        $existedProduct->getExtensionAttributes()->getTestStockItem()->getQty(),
                        $importedProduct->getExtensionAttributes()->getTestStockItem()->getQty()
                    );
                    $this->assertEquals(
                        $existedProduct->getExtensionAttributes()->getTestStockItem()->getStatus(),
                        $importedProduct->getExtensionAttributes()->getTestStockItem()->getStatus()
                    );
                }
            }
        }
    }

    /**
     * Remove all products in order to import ones and verify import doesn't loose any details.
     *
     * @return array
     */
    public function deleteProducts(): array
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $searchCriteria = $this->objectManager->create(SearchCriteriaBuilder::class)->create();
        $productToDelete = $productRepository->getList($searchCriteria)->getItems();
        foreach ($productToDelete as $product) {
            $productRepository->deleteById($product->getSku());
        }

        return $productToDelete;
    }

    /**
     * Get all imported products.
     *
     * @return array
     */
    public function getImportedProducts(): array
    {
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $searchCriteria = $this->objectManager->get(SearchCriteriaBuilder::class)->create();

        return $productRepository->getList($searchCriteria)->getItems();
    }
}

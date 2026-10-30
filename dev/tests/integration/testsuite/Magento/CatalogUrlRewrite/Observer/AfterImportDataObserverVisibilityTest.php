<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Integration test for URL rewrite cleanup when product visibility changes via CSV import.
 *
 * @see https://github.com/magento/magento2/issues/40533
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class AfterImportDataObserverVisibilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testUrlRewritesDeletedWhenVisibilityChangedToNotVisibleViaImport(): void
    {
        $productId = (int)$this->getProductRepository()->get('simple')->getId();

        $rewritesBeforeImport = $this->getProductUrlRewrites($productId);
        $this->assertGreaterThan(0, count($rewritesBeforeImport));

        $this->runImport('products_to_import_with_visibility_not_visible.csv');

        $rewritesAfterImport = $this->getProductUrlRewrites($productId);
        $this->assertCount(0, $rewritesAfterImport);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testUrlRewritesPreservedWhenVisibilityRemainsVisible(): void
    {
        $productId = (int)$this->getProductRepository()->get('simple')->getId();

        $rewritesBeforeImport = $this->getProductUrlRewrites($productId);
        $this->assertGreaterThan(0, count($rewritesBeforeImport));

        $this->runImport('products_to_import_with_visibility_visible.csv');

        $rewritesAfterImport = $this->getProductUrlRewrites($productId);
        $this->assertGreaterThan(0, count($rewritesAfterImport));
    }

    /**
     * Run CSV product import.
     *
     * @param string $fileName
     * @return void
     */
    private function runImport(string $fileName): void
    {
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../../CatalogImportExport/Model/Import/_files/' . $fileName,
                'directory' => $directory,
            ]
        );
        $importModel = $this->objectManager->create(Product::class);
        $importModel->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
            ]
        );
        $importModel->setSource($source);
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0, 'Import validation failed');
        $importModel->importData();
    }

    /**
     * Get all URL rewrites for a given product.
     *
     * @param int $productId
     * @return array
     */
    private function getProductUrlRewrites(int $productId): array
    {
        /** @var UrlFinderInterface $urlFinder */
        $urlFinder = $this->objectManager->get(UrlFinderInterface::class);

        return $urlFinder->findAllByData(
            [
                UrlRewrite::ENTITY_TYPE => 'product',
                UrlRewrite::ENTITY_ID => $productId,
            ]
        );
    }

    /**
     * @return ProductRepositoryInterface
     */
    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->objectManager->get(ProductRepositoryInterface::class);
    }
}

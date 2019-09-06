<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Indexer\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem;
use Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing as ExportAdvancedPricing;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ImportExport\Model\Export\Adapter\Csv as ExportAdapterCsv;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing as ImportAdvancedPricing;
use Magento\ImportExport\Model\Import\Source\Csv as ImportSourceCsv;
use Magento\ImportExport\Model\Import;

/**
 * Advanced pricing test
 */
class AdvancedPricingTest extends TestCase
{
    /**
     * @var ExportAdvancedPricing
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    // @codingStandardsIgnoreStart
    public static function setUpBeforeClass()
    {
        $db = Bootstrap::getInstance()
            ->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }
    // @codingStandardsIgnoreEnd

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->model = $this->objectManager->create(ExportAdvancedPricing::class);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testExport()
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $index = 0;
        $ids = [];
        $origPricingData = [];
        $skus = ['simple'];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])
                ->getId();
            $origPricingData[$index] = $this->objectManager->create(Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        $exportContent = $this->exportData($csvfile);
        $this->assertDiscountTypes($exportContent);

        $this->importData($csvfile);

        while ($index > 0) {
            $index--;
            $newPricingData = $this->objectManager->create(Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $this->assertEquals(count($origPricingData[$index]), count($newPricingData));
            $this->assertEqualsOtherThanSkippedAttributes($origPricingData[$index], $newPricingData, []);
        }
    }

    /**
     * Assert for correct tier prices discount types.
     *
     * @param string $exportContent
     * @return void
     */
    private function assertDiscountTypes($exportContent)
    {
        $this->assertContains(
            '2.0000,8.000000,Fixed',
            $exportContent
        );
        $this->assertContains(
            '10.0000,50.00,Discount',
            $exportContent
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/price/scope 1
     * @magentoDataFixture Magento/AdvancedPricingImportExport/_files/product_with_second_website.php
     */
    public function testExportMultipleWebsites()
    {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $index = 0;
        $ids = [];
        $origPricingData = [];
        $skus = ['AdvancedPricingSimple 1', 'AdvancedPricingSimple 2'];
        while (isset($skus[$index])) {
            $ids[$index] = $productRepository->get($skus[$index])
                ->getId();
            $origPricingData[$index] = $this->objectManager->create(Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $index++;
        }

        $csvfile = uniqid('importexport_') . '.csv';

        $exportContent = $this->exportData($csvfile);
        $this->assertContains(
            '"AdvancedPricingSimple 2",test,"ALL GROUPS",3.0000,5.0000',
            $exportContent
        );
        $this->importData($csvfile);

        while ($index > 0) {
            $index--;
            $newPricingData = $this->objectManager->create(Product::class)
                ->load($ids[$index])
                ->getTierPrices();
            $this->assertEquals(count($origPricingData[$index]), count($newPricingData));
            $this->assertEqualsOtherThanSkippedAttributes($origPricingData[$index], $newPricingData, []);
        }
    }

    /**
     * @param string $csvFile
     * @return string
     */
    private function exportData($csvFile)
    {
        $this->model->setWriter(
            Bootstrap::getObjectManager()
                ->create(
                    ExportAdapterCsv::class,
                    ['fileSystem' => $this->fileSystem, 'destination' => $csvFile]
                )
        );
        $exportContent = $this->model->export();
        $this->assertNotEmpty($exportContent);

        return $exportContent;
    }

    /**
     * @param string $csvFile
     */
    private function importData($csvFile)
    {
        /** @var ImportAdvancedPricing $importModel */
        $importModel = $this->objectManager->create(ImportAdvancedPricing::class);
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            ImportSourceCsv::class,
            [
                'file' => $csvFile,
                'directory' => $directory
            ]
        );
        $errors = $importModel->setParameters(
            [
                'behavior' => Import::BEHAVIOR_APPEND,
                'entity' => 'advanced_pricing'
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue(
            $errors->getErrorsCount() == 0,
            'Advanced Pricing import error, imported from file:' . $csvFile
        );
        $importModel->importData();
    }

    private function assertEqualsOtherThanSkippedAttributes($expected, $actual, $skippedAttributes)
    {
        foreach ($expected as $key => $value) {
            if (in_array($key, $skippedAttributes)) {
                continue;
            } else {
                $this->assertEquals(
                    $value,
                    $actual[$key],
                    'Assert value at key - ' . $key . ' failed'
                );
            }
        }
    }
}

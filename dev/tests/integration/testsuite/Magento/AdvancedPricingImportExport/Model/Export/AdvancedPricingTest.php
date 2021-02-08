<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
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
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    public static function setUpBeforeClass(): void
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

    protected function setUp(): void
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
        $this->assertStringContainsString('2.0000,8.000000,Fixed', $exportContent);
        $this->assertStringContainsString('10.0000,50.00,Discount', $exportContent);
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
        $this->assertStringContainsString('"AdvancedPricingSimple 2",test,"ALL GROUPS",3.0000,5.0000', $exportContent);
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
     * Export and Import of Advanced Pricing with different Price Types.
     *
     * @magentoDataFixture Magento/Catalog/_files/two_simple_products_with_tier_price.php
     * @return void
     */
    public function testExportImportOfAdvancedPricing(): void
    {
        $csvfile = uniqid('importexport_') . '.csv';
        $exportContent = $this->exportData($csvfile);
        $this->assertStringContainsString('second_simple,"All Websites [USD]","ALL GROUPS",10.0000,3.00,Discount', $exportContent);
        $this->assertStringContainsString('simple,"All Websites [USD]",General,5.0000,95.000000,Fixed', $exportContent);
        $this->updateTierPriceDataInCsv($csvfile);
        $this->importData($csvfile);

        /** @var  ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $firstProductTierPrices = $productRepository->get('simple')->getTierPrices();
        $secondProductTierPrices = $productRepository->get('second_simple')->getTierPrices();

        $this->assertSame(
            ['0', '1'],
            [
                $firstProductTierPrices[0]->getExtensionAttributes()->getWebsiteId(),
                $firstProductTierPrices[0]->getCustomerGroupId(),
            ]
        );

        $this->assertEquals(
            ['5.0000', '90.000000'],
            [
                $firstProductTierPrices[0]->getQty(),
                $firstProductTierPrices[0]->getValue(),
            ],
            '',
            0.1
        );

        $this->assertSame(
            ['0', \Magento\Customer\Model\Group::CUST_GROUP_ALL],
            [
                $secondProductTierPrices[0]->getExtensionAttributes()->getWebsiteId(),
                $secondProductTierPrices[0]->getCustomerGroupId(),
            ]
        );

        $this->assertEquals(
            ['5.00', '10.0000'],
            [
                $secondProductTierPrices[0]->getExtensionAttributes()->getPercentageValue(),
                $secondProductTierPrices[0]->getQty(),
            ],
            '',
            0.1
        );
    }

    /**
     * Update tier price data in CSV.
     *
     * @param string $csvfile
     * @return void
     */
    private function updateTierPriceDataInCsv(string $csvfile): void
    {
        $csvNewData = [
            0 => [
                0 => 'sku',
                1 => 'tier_price_website',
                2 => 'tier_price_customer_group',
                3 => 'tier_price_qty',
                4 => 'tier_price',
                5 => 'tier_price_value_type',
            ],
            1 => [
                0 => 'simple',
                1 => 'All Websites [USD]',
                2 => 'General',
                3 => '5',
                4 => '90',
                5 => 'Fixed',
            ],
            2 => [
                0 => 'second_simple',
                1 => 'All Websites [USD]',
                2 => 'ALL GROUPS',
                3 => '10',
                4 => '5',
                5 => 'Discount',
            ],
        ];

        /** @var Csv $csv */
        $csv = $this->objectManager->get(Csv::class);
        $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $csv->appendData($varDirectory->getAbsolutePath($csvfile), $csvNewData);
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

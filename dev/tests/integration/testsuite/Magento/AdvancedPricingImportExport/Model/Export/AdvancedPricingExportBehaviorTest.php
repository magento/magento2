<?php
declare(strict_types=1);
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedPricingImportExport\Model\Export;

use Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing as ExportAdvancedPricing;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\Adapter\Csv as ExportAdapterCsv;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration tests for advanced pricing export behavior.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedPricingExportBehaviorTest extends \PHPUnit\Framework\TestCase
{
    private const BULK_SKUS = [
        'adv-price-bulk-01',
        'adv-price-bulk-02',
        'adv-price-bulk-03',
        'adv-price-bulk-04',
        'adv-price-bulk-05',
        'adv-price-bulk-06',
        'adv-price-bulk-07',
        'adv-price-bulk-08',
        'adv-price-bulk-09',
        'adv-price-bulk-10',
    ];

    private const TIER_PRICES = [
        ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 1, 'value' => 99.99],
        ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 2, 'value' => 98.99],
        ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 3, 'value' => 97.99],
        ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 4, 'value' => 96.99],
        ['customer_group_id' => Group::NOT_LOGGED_IN_ID, 'qty' => 5, 'value' => 95.99],
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var Write
     */
    private $directory;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['sku' => 'simple'])
    ]
    public function testExportThrowsExceptionWhenNoTierPriceData(): void
    {
        $beforeFiles = $this->getAdvancedPricingExportFiles();

        /** @var Export $exportModel */
        $exportModel = $this->objectManager->create(Export::class);
        $exportModel->setData(
            [
                Export::FILTER_ELEMENT_GROUP => [],
                'entity' => 'advanced_pricing',
                'file_format' => 'csv',
            ]
        );

        try {
            $exportModel->export();
            $this->fail('Expected export to throw "There is no data for the export."');
        } catch (LocalizedException $exception) {
            $this->assertSame('There is no data for the export.', $exception->getMessage());
        }

        $afterFiles = $this->getAdvancedPricingExportFiles();
        $this->assertSame($beforeFiles, $afterFiles, 'Advanced pricing export must not leave an empty file behind.');
    }

    #[
        AppArea('adminhtml'),
        DbIsolation(true),
        AppIsolation(true),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[0],
                'name' => 'Advanced Pricing Bulk 1',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[1],
                'name' => 'Advanced Pricing Bulk 2',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[2],
                'name' => 'Advanced Pricing Bulk 3',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[3],
                'name' => 'Advanced Pricing Bulk 4',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p4'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[4],
                'name' => 'Advanced Pricing Bulk 5',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p5'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[5],
                'name' => 'Advanced Pricing Bulk 6',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p6'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[6],
                'name' => 'Advanced Pricing Bulk 7',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p7'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[7],
                'name' => 'Advanced Pricing Bulk 8',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p8'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[8],
                'name' => 'Advanced Pricing Bulk 9',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p9'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => self::BULK_SKUS[9],
                'name' => 'Advanced Pricing Bulk 10',
                'price' => 200,
                'tier_prices' => self::TIER_PRICES,
            ],
            as: 'p10'
        )
    ]
    public function testExportTenProductsWithFiveTierPricesEach(): void
    {
        $csvFile = uniqid('importexport_', true) . '.csv';
        try {
            $exportContent = $this->exportData($csvFile);
            $rows = $this->parseCsvRows($exportContent);

            $header = array_shift($rows);
            $skuIndex = array_search('sku', $header, true);
            $this->assertNotFalse($skuIndex, 'SKU column not found in export header');

            $bulkRows = array_values(array_filter($rows, static function (array $row) use ($skuIndex): bool {
                return isset($row[$skuIndex]) && in_array($row[$skuIndex], self::BULK_SKUS, true);
            }));

            $this->assertCount(50, $bulkRows);

            $rowsPerSku = [];
            foreach ($bulkRows as $row) {
                $rowsPerSku[$row[$skuIndex]] = ($rowsPerSku[$row[$skuIndex]] ?? 0) + 1;
            }

            $this->assertCount(10, $rowsPerSku);
            foreach ($rowsPerSku as $count) {
                $this->assertSame(5, $count);
            }
        } finally {
            $this->deleteExportFile($csvFile);
        }
    }

    /**
     * @param string $csvFile
     * @return string
     */
    private function exportData(string $csvFile): string
    {
        $writer = Bootstrap::getObjectManager()->create(ExportAdapterCsv::class, ['fileSystem' => $this->fileSystem]);
        $model = $this->objectManager->create(ExportAdvancedPricing::class);
        $model->setWriter($writer);
        $exportContent = $model->export();
        $this->assertNotEmpty($exportContent);

        $driver = $this->directory->getDriver();
        $driver->filePutContents($this->directory->getAbsolutePath($csvFile), $exportContent);

        return $exportContent;
    }

    /**
     * Parse CSV content to rows.
     *
     * @param string $csvContent
     * @return array
     */
    private function parseCsvRows(string $csvContent): array
    {
        $handle = fopen('php://temp', 'rb+');
        fwrite($handle, $csvContent);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Delete exported CSV file if it was created.
     *
     * @param string $csvFile
     * @return void
     */
    private function deleteExportFile(string $csvFile): void
    {
        $driver = $this->directory->getDriver();
        $filePath = $this->directory->getAbsolutePath($csvFile);
        if ($driver->isExists($filePath)) {
            $driver->deleteFile($filePath);
        }
    }

    /**
     * @return string[]
     */
    private function getAdvancedPricingExportFiles(): array
    {
        $files = glob($this->directory->getAbsolutePath('export/advanced_pricing_*.csv')) ?: [];
        sort($files);
        return $files;
    }
}

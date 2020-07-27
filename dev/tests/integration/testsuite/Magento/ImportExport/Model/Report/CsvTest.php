<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Report;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class CsvTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var Csv
     */
    private $csvReport;

    /**
     * @var string|null
     */
    private $importFilePath;

    /**
     * @var string|null
     */
    private $reportPath;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $filesystem = Bootstrap::getObjectManager()->create(Filesystem::class);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $this->csvReport = Bootstrap::getObjectManager()->create(Csv::class);
    }
    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ([$this->importFilePath, $this->reportPath] as $path) {
            if ($path && $this->directory->isExist($path)) {
                $this->directory->delete($path);
            }
        }
    }

    /**
     * @return void
     */
    public function testCreateReport()
    {
        $importData = <<<fileContent
sku,store_view_code,name,price,product_type,attribute_set_code,weight
simple1,,"simple 1",10,simple,Default,-5
fileContent;
        $this->importFilePath = 'test_import.csv';
        $this->directory->writeFile($this->importFilePath, $importData);

        $errorAggregator = Bootstrap::getObjectManager()->create(ProcessingErrorAggregatorInterface::class);
        $error = 'Value for \'weight\' attribute contains incorrect value';
        $errorAggregator->addError($error, ProcessingError::ERROR_LEVEL_CRITICAL, 1, 'weight', $error);

        $outputFileName = $this->csvReport->createReport(
            $this->directory->getAbsolutePath($this->importFilePath),
            $errorAggregator
        );

        $this->reportPath = Import::IMPORT_HISTORY_DIR . $outputFileName;
        $this->assertTrue($this->directory->isExist($this->reportPath), 'Report was not generated');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Integration\Model\Export;

use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use Magento\InventoryImportExport\Model\Export\Sources;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourcesTest extends TestCase
{
    /**
     * @var Sources
     */
    private $exporter;

    /**
     * @var string
     */
    private $exportFilePath;

    protected function setUp()
    {
        $sandboxDir = Bootstrap::getInstance()->getBootstrap()->getApplication()->getTempDir();
        $this->exportFilePath = implode(DIRECTORY_SEPARATOR, [
            $sandboxDir,
            'var',
            uniqid('test-export_', false) . '.csv'
        ]);

        $this->exporter = Bootstrap::getObjectManager()->create(Sources::class);
        $this->exporter->setWriter(Bootstrap::getObjectManager()->create(
            Csv::class,
            ['destination' => $this->exportFilePath]
        ));
    }

    protected function tearDown()
    {
        unlink($this->exportFilePath);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExportWithoutAnyFiltering()
    {
        $this->exporter->setParameters([]);
        $this->exporter->export();

        $exportFullLines = file(
            implode(DIRECTORY_SEPARATOR, [__DIR__, '_files', 'export_full.csv']),
            FILE_IGNORE_NEW_LINES
        );

        foreach ($exportFullLines as $line) {
            $this->assertContains(
                $line,
                file_get_contents($this->exportFilePath)
            );
        }
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExportWithSkuFilter()
    {
        $this->exporter->setParameters([
            Export::FILTER_ELEMENT_GROUP => [
                'sku' => 'SKU-1'
            ]
        ]);
        $this->exporter->export();

        $this->assertEquals(
            file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '_files', 'export_filtered_by_sku.csv'])),
            file_get_contents($this->exportFilePath)
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExportWithSkuFilterByLikeQuery()
    {
        $this->exporter->setParameters([
            Export::FILTER_ELEMENT_GROUP => [
                'sku' => 'U-1'
            ]
        ]);
        $this->exporter->export();

        $this->assertEquals(
            file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '_files', 'export_filtered_by_sku.csv'])),
            file_get_contents($this->exportFilePath)
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExportWithSourceFilter()
    {
        $this->exporter->setParameters([
            Export::FILTER_ELEMENT_GROUP => [
                'source_code' => 'eu'
            ]
        ]);
        $this->exporter->export();

        $this->assertEquals(
            file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '_files', 'export_filtered_by_source.csv'])),
            file_get_contents($this->exportFilePath)
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExportFilteredWithoutStatusColumn()
    {
        $this->exporter->setParameters([
            Export::FILTER_ELEMENT_GROUP => [
                'sku' => 'SKU-1',
                'status' => 1
            ],
            Export::FILTER_ELEMENT_SKIP => [
                'status'
            ]
        ]);
        $this->exporter->export();

        $this->assertEquals(
            file_get_contents(implode(DIRECTORY_SEPARATOR, [
                __DIR__,
                '_files',
                'export_filtered_without_status_column.csv'
            ])),
            file_get_contents($this->exportFilePath)
        );
    }
}

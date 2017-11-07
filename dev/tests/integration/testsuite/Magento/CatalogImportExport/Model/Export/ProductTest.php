<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * Stock item attributes which must be exported
     *
     * @var array
     */
    public static $stockItemAttributes = [
        'qty',
        'min_qty',
        'use_config_min_qty',
        'is_qty_decimal',
        'backorders',
        'use_config_backorders',
        'min_sale_qty',
        'use_config_min_sale_qty',
        'max_sale_qty',
        'use_config_max_sale_qty',
        'is_in_stock',
        'notify_stock_qty',
        'use_config_notify_stock_qty',
        'manage_stock',
        'use_config_manage_stock',
        'use_config_qty_increments',
        'qty_increments',
        'use_config_enable_qty_inc',
        'enable_qty_increments',
        'is_decimal_divided'
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(\Magento\Framework\Filesystem::class);
        $this->model = $this->objectManager->create(
            \Magento\CatalogImportExport\Model\Export\Product::class
        );
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     * @magentoDbIsolationEnabled
     */
    public function testExport()
    {
        $this->model->setWriter(
            $this->objectManager->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $exportData = $this->model->export();
        $this->assertContains('New Product', $exportData);

        $this->assertContains('Option 1 & Value 1"', $exportData);
        $this->assertContains('Option 1 & Value 2"', $exportData);
        $this->assertContains('Option 1 & Value 3"', $exportData);
        $this->assertContains('Option 4 ""!@#$%^&*', $exportData);
        $this->assertContains('test_option_code_2', $exportData);
        $this->assertContains('max_characters=10', $exportData);
        $this->assertContains('text_attribute=!@#$%^&*()_+1234567890-=|\\:;""\'<,>.?/', $exportData);
        $occurrencesCount = substr_count($exportData, 'Hello "" &"" Bring the water bottle when you can!');
        $this->assertEquals(1, $occurrencesCount);
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data_special_chars.php
     * @magentoDbIsolationEnabled
     */
    public function testExportSpecialChars()
    {
        $this->model->setWriter(
            $this->objectManager->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $exportData = $this->model->export();
        $this->assertContains('simple ""1""', $exportData);
        $this->assertContains('Category with slash\/ symbol', $exportData);
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_with_product_links_data.php
     * @magentoDbIsolationEnabled
     */
    public function testExportWithProductLinks()
    {
        $this->model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $this->assertNotEmpty($this->model->export());
    }

    /**
     * Verify that all stock item attribute values are exported (aren't equal to empty string)
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @covers \Magento\CatalogImportExport\Model\Export\Product::export
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExportStockItemAttributesAreFilled()
    {
        $this->markTestSkipped('Test needs to be skipped.');
        $fileWrite = $this->createMock(\Magento\Framework\Filesystem\File\Write::class);
        $directoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['getParentDirectory', 'isWritable', 'isFile', 'readFile', 'openFile']
        );
        $directoryMock->expects($this->any())->method('getParentDirectory')->will($this->returnValue('some#path'));
        $directoryMock->expects($this->any())->method('isWritable')->will($this->returnValue(true));
        $directoryMock->expects($this->any())->method('isFile')->will($this->returnValue(true));
        $directoryMock->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue('some string read from file')
        );
        $directoryMock->expects($this->once())->method('openFile')->will($this->returnValue($fileWrite));

        $filesystemMock = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $filesystemMock->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

        $exportAdapter = new \Magento\ImportExport\Model\Export\Adapter\Csv($filesystemMock);

        $this->model->setWriter($exportAdapter)->export();
    }

    /**
     * Verify header columns (that stock item attributes column headers are present)
     *
     * @param array $headerColumns
     */
    public function verifyHeaderColumns(array $headerColumns)
    {
        foreach (self::$stockItemAttributes as $stockItemAttribute) {
            $this->assertContains(
                $stockItemAttribute,
                $headerColumns,
                "Stock item attribute {$stockItemAttribute} is absent among header columns"
            );
        }
    }

    /**
     * Verify row data (stock item attribute values)
     *
     * @param array $rowData
     */
    public function verifyRow(array $rowData)
    {
        foreach (self::$stockItemAttributes as $stockItemAttribute) {
            $this->assertNotSame(
                '',
                $rowData[$stockItemAttribute],
                "Stock item attribute {$stockItemAttribute} value is empty string"
            );
        }
    }

    /**
     * Verifies if exception processing works properly
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExceptionInGetExportData()
    {
        $this->markTestSkipped('Test needs to be skipped.');
        $exception = new \Exception('Error');

        $rowCustomizerMock =
            $this->getMockBuilder(\Magento\CatalogImportExport\Model\Export\RowCustomizerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $directoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['getParentDirectory', 'isWritable']
        );
        $directoryMock->expects($this->any())->method('getParentDirectory')->will($this->returnValue('some#path'));
        $directoryMock->expects($this->any())->method('isWritable')->will($this->returnValue(true));

        $filesystemMock = $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite']);
        $filesystemMock->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

        $exportAdapter = new \Magento\ImportExport\Model\Export\Adapter\Csv($filesystemMock);

        $rowCustomizerMock->expects($this->once())->method('prepareData')->willThrowException($exception);
        $loggerMock->expects($this->once())->method('critical')->with($exception);

        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        /** @var \Magento\CatalogImportExport\Model\Export\Product $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogImportExport\Model\Export\Product::class,
            [
                'rowCustomizer' => $rowCustomizerMock,
                'logger' => $loggerMock,
                'collection' => $collection
            ]
        );

        $data = $model->setWriter($exportAdapter)->export();
        $this->assertEmpty($data);
    }

    /**
     * Verify if fields wrapping works correct when "Fields Enclosure" option enabled
     *
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExportWithFieldsEnclosure()
    {
        $this->model->setParameters([
            \Magento\ImportExport\Model\Export::FIELDS_ENCLOSURE => 1
        ]);

        $this->model->setWriter(
            $this->objectManager->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );
        $exportData = $this->model->export();

        $this->assertContains('""Option 2""', $exportData);
        $this->assertContains('""Option 3""', $exportData);
        $this->assertContains('""Option 4 """"!@#$%^&*""', $exportData);
        $this->assertContains('text_attribute=""!@#$%^&*()_+1234567890-=|\:;""""\'<,>.?/', $exportData);
    }

    /**
     * Verify that "category ids" filter correctly applies to export result
     *
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_with_categories.php
     */
    public function testCategoryIdsFilter()
    {
        $this->model->setWriter(
            $this->objectManager->create(
                \Magento\ImportExport\Model\Export\Adapter\Csv::class
            )
        );

        $this->model->setParameters([
            \Magento\ImportExport\Model\Export::FILTER_ELEMENT_GROUP => [
                'category_ids' => '2,13'
            ]
        ]);

        $exportData = $this->model->export();

        $this->assertContains('Simple Product', $exportData);
        $this->assertContains('Simple Product Three', $exportData);
        $this->assertNotContains('Simple Product Two', $exportData);
        $this->assertNotContains('Simple Product Not Visible On Storefront', $exportData);
    }
}

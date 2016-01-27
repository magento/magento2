<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;

/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product
     */
    protected $_model;

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
        'is_decimal_divided',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExport()
    {
        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv'
            )
        );
        $this->assertNotEmpty($this->_model->export());
    }

    /**
     * Verify that all stock item attribute values are exported (aren't equal to empty string)
     *
     * @covers \Magento\CatalogImportExport\Model\Export\Product::export
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExportStockItemAttributesAreFilled()
    {
        $fileWrite = $this->getMock('Magento\Framework\Filesystem\File\Write', [], [], '', false);
        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
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

        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystemMock->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

        $exportAdapter = new \Magento\ImportExport\Model\Export\Adapter\Csv($filesystemMock);

        $this->_model->setWriter($exportAdapter)->export();
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
     *
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExceptionInGetExportData()
    {
        $exception = new \Exception('Error');

        $rowCustomizerMock = $this->getMockBuilder('Magento\CatalogImportExport\Model\Export\RowCustomizerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')->getMock();

        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $directoryMock->expects($this->any())->method('getParentDirectory')->will($this->returnValue('some#path'));
        $directoryMock->expects($this->any())->method('isWritable')->will($this->returnValue(true));

        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystemMock->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

        $exportAdapter = new \Magento\ImportExport\Model\Export\Adapter\Csv($filesystemMock);

        $rowCustomizerMock->expects($this->once())->method('prepareData')->willThrowException($exception);
        $loggerMock->expects($this->once())->method('critical')->with($exception);

        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );

        /** @var \Magento\CatalogImportExport\Model\Export\Product $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Export\Product',
            [
                'rowCustomizer' => $rowCustomizerMock,
                'logger' => $loggerMock,
                'collection' => $collection
            ]
        );

        $data = $model->setWriter($exportAdapter)->export();
        $this->assertEmpty($data);
    }
}

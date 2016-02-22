<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * skipped attributes
     *
     * @var array
     */
    public static $skippedAttributes = [
        'options',
        'updated_at',
        'extension_attributes',
    ];

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

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
    }

    /**
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_data.php
     */
    public function testExport()
    {
        $productRepository = $this->objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        );
        $id1 = $productRepository->get('simple_ms_1')->getId();
        $origProduct1Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id1)->getData();
        $id2 = $productRepository->get('simple_ms_2')->getId();
        $origProduct2Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id2)->getData();
        $id3 = $productRepository->get('simple')->getId();
        $origProduct3Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id3)->getData();

        $fileSystem = $this->objectManager->get('Magento\Framework\Filesystem');
        $csvfile = uniqid('importexport_');

        $this->_model->setWriter(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\ImportExport\Model\Export\Adapter\Csv',
                ['fileSystem' => $fileSystem, 'destination' => $csvfile]
            )
        );
        $this->assertNotEmpty($this->_model->export());

        /** @var \Magento\CatalogImportExport\Model\Import\Product $importModel */
        $importModel = $this->objectManager->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
        $directory = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $csvfile,
                'directory' => $directory
            ]
        );
        $errors = $importModel->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $importModel->importData();
        $newProduct1Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id1)->getData();
        $newProduct2Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id2)->getData();
        $newProduct3Data = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id3)->getData();

        $this->assertEquals(count($origProduct1Data), count($newProduct1Data));
        $this->assertEquals(count($origProduct2Data), count($newProduct2Data));
        $this->assertEquals(count($origProduct3Data), count($newProduct3Data));
        $this->assertEqualsOtherThanUpdatedAt($origProduct1Data, $newProduct1Data);
        $this->assertEqualsOtherThanUpdatedAt($origProduct2Data, $newProduct2Data);
        $this->assertEqualsOtherThanUpdatedAt($origProduct3Data, $newProduct3Data);
    }

    private function assertEqualsOtherThanUpdatedAt($expected, $actual)
    {
        foreach ($expected as $key => $value) {
            if (in_array($key, self::$skippedAttributes)) {
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

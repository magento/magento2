<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogImportExport\Model\Export;

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
    public static $stockItemAttributes = array(
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
    );

    protected function setUp()
    {
        parent::setUp();

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Export\Product'
        );
    }

    /**
     * @magentoDataFixture Magento/ImportExport/_files/product.php
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
     * @magentoDataFixture Magento/ImportExport/_files/product.php
     */
    public function testExportStockItemAttributesAreFilled()
    {
        $filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $directoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', array(), array(), '', false);

        $filesystemMock->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($directoryMock));

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
}

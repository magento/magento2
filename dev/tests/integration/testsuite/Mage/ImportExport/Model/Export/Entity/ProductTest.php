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
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_ImportExport_Model_Export_Entity_ProductTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Export_Entity_Product
     */
    protected $_model;

    /**
     * Store old display_errors ini option value here
     *
     * @var int
     */
    protected $_oldDisplayErrors;

    /**
     * Store old error_reporting ini option value here
     *
     * @var int
     */
    protected $_oldErrorLevel;

    /**
     * Store old isDeveloperMode value here
     *
     * @var boolean
     */
    protected $_oldIsDeveloperMode;

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
        'is_decimal_divided',
    );

    protected function setUp()
    {
        parent::setUp();

        $this->_model = Mage::getModel('Mage_ImportExport_Model_Export_Entity_Product');

        $this->_oldDisplayErrors  = ini_get('display_errors');
        $this->_oldErrorLevel = error_reporting();
        $this->_oldIsDeveloperMode = Mage::getIsDeveloperMode();
    }

    protected function tearDown()
    {
        ini_set('display_errors', $this->_oldDisplayErrors);
        error_reporting($this->_oldErrorLevel);
        Mage::setIsDeveloperMode($this->_oldIsDeveloperMode);
        unset($this->_model);

        parent::tearDown();
    }

    /**
     * Test that there is no notice in _updateDataWithCategoryColumns()
     *
     * @covers Mage_ImportExport_Model_Export_Entity_Product::_updateDataWithCategoryColumns
     *
     * @magentoDataFixture Mage/ImportExport/_files/product.php
     */
    public function testExport()
    {
        // we have to set strict error reporting mode and enable mage developer mode to convert notice to exception
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 1);
        Mage::setIsDeveloperMode(true);

        $this->_model->setWriter(Mage::getModel('Mage_ImportExport_Model_Export_Adapter_Csv'));
        $this->assertNotEmpty($this->_model->export());
    }

    /**
     * Verify that all stock item attribute values are exported (aren't equal to empty string)
     *
     * @covers Mage_ImportExport_Model_Export_Entity_Product::export
     * @magentoDataFixture Mage/ImportExport/_files/product.php
     */
    public function testExportStockItemAttributesAreFilled()
    {
        $writerMock = $this->getMockForAbstractClass(
            'Mage_ImportExport_Model_Export_Adapter_Abstract',
            array(),
            '',
            true,
            true,
            true,
            array('setHeaderCols', 'writeRow')
        );

        $writerMock->expects($this->any())
            ->method('setHeaderCols')
            ->will($this->returnCallback(array($this, 'verifyHeaderColumns')));

        $writerMock->expects($this->any())
            ->method('writeRow')
            ->will($this->returnCallback(array($this, 'verifyRow')));

        $this->_model->setWriter($writerMock)
            ->export();
    }

    /**
     * Verify header columns (that stock item attributes column headers are present)
     *
     * @param array $headerColumns
     */
    public function verifyHeaderColumns(array $headerColumns)
    {
        foreach (self::$stockItemAttributes as $stockItemAttribute) {
            $this->assertContains($stockItemAttribute, $headerColumns,
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
            $this->assertNotSame('', $rowData[$stockItemAttribute],
                "Stock item attribute {$stockItemAttribute} value is empty string"
            );
        }
    }
}

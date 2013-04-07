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
 * @package     Magento_Tax
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tax_Model_Rate_CsvImportHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Tax_Model_Rate_CsvImportHandler
     */
    protected $_importHandler;

    protected function setUp()
    {
        $this->_importHandler = Mage::getModel('Mage_Tax_Model_Rate_CsvImportHandler');
    }

    protected function tearDown()
    {
        $this->_importHandler = null;
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testImportFromCsvFileWithCorrectData()
    {
        $importFileName = __DIR__ . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'correct_rates_import_file.csv';
        $this->_importHandler->importFromCsvFile(array('tmp_name' => $importFileName));

        // assert that both tax rates, specified in import file, have been imported correctly
        $importedRuleCA = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->loadByCode('US-CA-*-Rate Import Test');
        $this->assertNotEmpty($importedRuleCA->getId());
        $this->assertEquals(8.25, (float)$importedRuleCA->getRate());
        $this->assertEquals('US', $importedRuleCA->getTaxCountryId());

        $importedRuleFL = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->loadByCode('US-FL-*-Rate Import Test');
        $this->assertNotEmpty($importedRuleFL->getId());
        $this->assertEquals(15, (float)$importedRuleFL->getRate());
        $this->assertEquals('US', $importedRuleFL->getTaxCountryId());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage One of the countries has invalid code.
     */
    public function testImportFromCsvFileThrowsExceptionWhenCountryCodeIsInvalid()
    {
        $importFileName = __DIR__ . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'rates_import_file_incorrect_country.csv';
        $this->_importHandler->importFromCsvFile(array('tmp_name' => $importFileName));
    }
}

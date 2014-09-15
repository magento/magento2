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
namespace Magento\TaxImportExport\Model\Rate;

class CsvImportHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Rate\CsvImportHandler
     */
    protected $_importHandler;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_importHandler = $objectManager->create('Magento\TaxImportExport\Model\Rate\CsvImportHandler');
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
        $importFileName = __DIR__ . '/_files/correct_rates_import_file.csv';
        $this->_importHandler->importFromCsvFile(array('tmp_name' => $importFileName));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        // assert that both tax rates, specified in import file, have been imported correctly
        $importedRuleCA = $objectManager->create(
            'Magento\Tax\Model\Calculation\Rate'
        )->loadByCode(
            'US-CA-*-Rate Import Test'
        );
        $this->assertNotEmpty($importedRuleCA->getId());
        $this->assertEquals(8.25, (double)$importedRuleCA->getRate());
        $this->assertEquals('US', $importedRuleCA->getTaxCountryId());
        $this->assertEquals('*', $importedRuleCA->getTaxPostcode());

        $importedRuleFL = $objectManager->create(
            'Magento\Tax\Model\Calculation\Rate'
        )->loadByCode(
            'US-FL-*-Rate Import Test'
        );
        $this->assertNotEmpty($importedRuleFL->getId());
        $this->assertEquals(15, (double)$importedRuleFL->getRate());
        $this->assertEquals('US', $importedRuleFL->getTaxCountryId());
        $this->assertNull($importedRuleFL->getTaxPostcode());
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Model\Exception
     * @expectedExceptionMessage One of the countries has invalid code.
     */
    public function testImportFromCsvFileThrowsExceptionWhenCountryCodeIsInvalid()
    {
        $importFileName = __DIR__ . '/_files/rates_import_file_incorrect_country.csv';
        $this->_importHandler->importFromCsvFile(array('tmp_name' => $importFileName));
    }
}

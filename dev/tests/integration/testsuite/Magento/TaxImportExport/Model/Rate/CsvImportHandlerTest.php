<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Model\Rate;

class CsvImportHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TaxImportExport\Model\Rate\CsvImportHandler
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
        $this->_importHandler->importFromCsvFile(['tmp_name' => $importFileName]);

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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage One of the countries has invalid code.
     */
    public function testImportFromCsvFileThrowsExceptionWhenCountryCodeIsInvalid()
    {
        $importFileName = __DIR__ . '/_files/rates_import_file_incorrect_country.csv';
        $this->_importHandler->importFromCsvFile(['tmp_name' => $importFileName]);
    }
}

<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DobColumnTest extends TestCase
{
    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->uiComponentFactory = Bootstrap::getObjectManager()->create(UiComponentFactory::class);
    }

    /**
     * Test that dateFormat is not defined in the XML configuration for dob column.
     *
     * This test verifies that the explicit dateFormat configuration was removed from the XML
     * and that the date format is now handled programmatically by the ColumnFactory.
     *
     * @return void
     */
    public function testDobColumnDoesNotHaveDateFormatInXml(): void
    {
        $xmlPath = BP . '/app/code/Magento/Customer/view/adminhtml/ui_component/customer_listing.xml';
        $this->assertFileExists($xmlPath, 'Customer listing XML file should exist');

        $xmlContent = file_get_contents($xmlPath);
        $this->assertNotFalse($xmlContent, 'Should be able to read XML file');

        $xml = simplexml_load_string($xmlContent);
        $this->assertNotFalse($xml, 'XML should be valid');

        // Find the dob column in the XML
        $dobColumn = null;
        foreach ($xml->xpath('//column[@name="dob"]') as $column) {
            $dobColumn = $column;
            break;
        }

        $this->assertNotNull($dobColumn, 'DOB column should exist in XML');

        // Verify that dateFormat is not explicitly set in the XML
        $dateFormatNodes = $dobColumn->xpath('.//dateFormat');
        $this->assertEmpty($dateFormatNodes, 'dateFormat should not be explicitly set in XML for dob column');

        // Verify that other expected settings are still present
        $timezoneNodes = $dobColumn->xpath('.//timezone');
        $this->assertNotEmpty($timezoneNodes, 'timezone setting should still be present');
        $this->assertEquals('false', (string)$timezoneNodes[0], 'timezone should be set to false');

        $skipTimeZoneNodes = $dobColumn->xpath('.//skipTimeZoneConversion');
        $this->assertNotEmpty($skipTimeZoneNodes, 'skipTimeZoneConversion setting should still be present');
        $this->assertEquals('true', (string)$skipTimeZoneNodes[0], 'skipTimeZoneConversion should be set to true');
    }
}

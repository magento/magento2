<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Layout;

class FilesLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verifying that in page_layout directory contains only page layout xml files
     *
     * @param string $filename
     * @dataProvider pageLayoutFilesDataProvider
     */
    public function testPageLayoutFiles($filename)
    {
        $validSchema = 'Magento/Framework/View/Layout/etc/page_layout.xsd';
        $xmlFile = file_get_contents($filename);
        $schemaLocations = [];
        preg_match('/xsi:noNamespaceSchemaLocation=\s*"([^"]+)"/s', $xmlFile, $schemaLocations);
        $this->assertEquals(
            2,
            count($schemaLocations),
            'The XML file at ' . $filename . ' does not have a schema defined.'
        );

        $schemaFile = dirname($filename).'/'.$schemaLocations[1];
        $this->assertFileExists($schemaFile, "$filename refers to an invalid schema $schemaFile.");

        $schemaFile = realpath($schemaFile);
        $this->assertNotFalse(
            strpos($schemaFile, $validSchema),
            '"page_layout" directory should contain only page layout xml files'
        );
    }

    public function pageLayoutFilesDataProvider()
    {
        return \Magento\Framework\Test\Utility\Files::init()->getPageLayoutFiles();
    }

    /**
     * Verifying that in layout directory contains only page page configuration and generic layout xml files
     *
     * @param string $filename
     * @dataProvider pageConfigurationAndGenericLayoutFilesDataProvider
     */
    public function testPageConfigurationAndGenericLayoutFiles($filename)
    {
        $validSchema = [
            'Magento/Framework/View/Layout/etc/page_configuration.xsd',
            'Magento/Framework/View/Layout/etc/layout_generic.xsd'
        ];
        $xmlFile = file_get_contents($filename);
        $schemaLocations = [];
        preg_match('/xsi:noNamespaceSchemaLocation=\s*"([^"]+)"/s', $xmlFile, $schemaLocations);
        $this->assertEquals(
            2,
            count($schemaLocations),
            'The XML file at ' . $filename . ' does not have a schema defined.'
        );

        $schemaFile = dirname($filename).'/'.$schemaLocations[1];
        $this->assertFileExists($schemaFile, "$filename refers to an invalid schema $schemaFile.");

        $schemaFile = realpath($schemaFile);
        $this->assertTrue(
            false !== strpos($schemaFile, $validSchema[0]) || false !== strpos($schemaFile, $validSchema[1]),
            '"layout" directory should contain only page configuration and generic layout xml files'
        );
    }

    public function pageConfigurationAndGenericLayoutFilesDataProvider()
    {
        return \Magento\Framework\Test\Utility\Files::init()->getLayoutFiles();
    }
}

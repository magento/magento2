<?php
/**
 * Find "widget.xml" files and validate them
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Widget;

class WidgetConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testXmlFiles()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $configFile
             */
            function ($configFile) {
                $schema = \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
                    '/app/code/Magento/Widget/etc/widget.xsd';
                $this->_validateFileExpectSuccess($configFile, $schema);
            },
            array_merge(
                \Magento\Framework\Test\Utility\Files::init()->getConfigFiles('widget.xml'),
                \Magento\Framework\Test\Utility\Files::init()->getLayoutConfigFiles('widget.xml')
            )
        );
    }

    public function testSchemaUsingValidXml()
    {
        $xmlFile = __DIR__ . '/_files/widget.xml';
        $schema = \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
            '/app/code/Magento/Widget/etc/widget.xsd';
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
            '/app/code/Magento/Widget/etc/widget.xsd';
        $this->_validateFileExpectFailure($xmlFile, $schema);
    }

    public function testFileSchemaUsingXml()
    {
        $xmlFile = __DIR__ . '/_files/widget_file.xml';
        $schema = \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
            '/app/code/Magento/Widget/etc/widget_file.xsd';
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testFileSchemaUsingInvalidXml()
    {
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = \Magento\Framework\Test\Utility\Files::init()->getPathToSource() .
            '/app/code/Magento/Widget/etc/widget_file.xsd';
        $this->_validateFileExpectFailure($xmlFile, $schema);
    }

    /**
     * Run schema validation against an xml file with a provided schema.
     *
     * This helper expects the validation to pass and will fail a test if any errors are found.
     *
     * @param $xmlFile string a known good xml file.
     * @param $schemaFile string schema that should find no errors in the known good xml file.
     */
    protected function _validateFileExpectFailure($xmlFile, $schemaFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaFile);
        if (!$errors) {
            $this->fail('There is a problem with the schema.  A known bad XML file passed validation');
        }
    }

    /**
     * Run schema validation against a known bad xml file with a provided schema.
     *
     * This helper expects the validation to fail and will fail a test if no errors are found.
     *
     * @param $xmlFile string a known bad xml file.
     * @param $schemaFile string schema that should find errors in the known bad xml file.
     */
    protected function _validateFileExpectSuccess($xmlFile, $schemaFile)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $errors = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaFile);
        if ($errors) {
            $this->fail(
                'There is a problem with the schema.  A known good XML file failed validation: ' . PHP_EOL . implode(
                    PHP_EOL . PHP_EOL,
                    $errors
                )
            );
        }
    }
}

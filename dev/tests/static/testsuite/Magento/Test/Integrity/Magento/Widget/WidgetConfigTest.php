<?php
/**
 * Find "widget.xml" files and validate them
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Widget;

class WidgetConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    public function testXmlFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $configFile
             */
            function ($configFile) {
                $schema = $this->urnResolver->getRealPath('urn:magento:module:Magento_Widget:etc/widget.xsd');
                $this->_validateFileExpectSuccess($configFile, $schema);
            },
            array_merge(
                \Magento\Framework\App\Utility\Files::init()->getConfigFiles('widget.xml'),
                \Magento\Framework\App\Utility\Files::init()->getLayoutConfigFiles('widget.xml')
            )
        );
    }

    public function testSchemaUsingValidXml()
    {
        $xmlFile = __DIR__ . '/_files/widget.xml';
        $schema = $this->urnResolver->getRealPath('urn:magento:module:Magento_Widget:etc/widget.xsd');
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testSchemaUsingInvalidXml()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped due to MAGETWO-45033');
        }
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = $this->urnResolver->getRealPath('urn:magento:module:Magento_Widget:etc/widget.xsd');
        $this->_validateFileExpectFailure($xmlFile, $schema);
    }

    public function testFileSchemaUsingXml()
    {
        $xmlFile = __DIR__ . '/_files/widget_file.xml';
        $schema = $this->urnResolver->getRealPath('urn:magento:module:Magento_Widget:etc/widget_file.xsd');
        $this->_validateFileExpectSuccess($xmlFile, $schema);
    }

    public function testFileSchemaUsingInvalidXml()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped due to MAGETWO-45033');
        }
        $xmlFile = __DIR__ . '/_files/invalid_widget.xml';
        $schema = $this->urnResolver->getRealPath('urn:magento:module:Magento_Widget:etc/widget_file.xsd');
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

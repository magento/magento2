<?php
/**
 * Test persistent.xsd and xml files.
 *
 * Find "persistent.xml" files in code tree and validate them.  Also verify schema fails on an invalid xml and
 * passes on a valid xml.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Persistent;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:module:Magento_Persistent:etc/persistent.xsd');
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/valid_persistent.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid_persistent.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return '';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return '';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return '';
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'persistent.xml';
    }

    public function testFileSchemaUsingInvalidXml($expectedErrors = null)
    {
        $this->markTestSkipped('persistent.xml does not have a partial schema');
    }

    public function testSchemaUsingPartialXml($expectedErrors = null)
    {
        $this->markTestSkipped('persistent.xml does not have a partial schema');
    }

    public function testFileSchemaUsingPartialXml()
    {
        $this->markTestSkipped('persistent.xml does not have a partial schema');
    }

    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = [
            "Element 'welcome': This element is not expected.",
            "Element 'models': This element is not expected.",
        ];
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }
}

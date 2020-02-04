<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Webapi\Model;

use Magento\TestFramework\Integrity\AbstractConfig;

/**
 * Find webapi xml files and validate them
 */
class ConfigTest extends AbstractConfig
{
    /**
     * @inheritdoc
     */
    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        // @codingStandardsIgnoreStart
        $expectedErrors = [
            "Element 'route', attribute 'method': [facet 'enumeration'] The value 'PATCH' is not an element of the set {'GET', 'PUT', 'POST', 'DELETE'}.",
            "Element 'route', attribute 'method': 'PATCH' is not a valid value of the local atomic type.",
            "Element 'service': The attribute 'method' is required but missing.",
            "Element 'data': Missing child element(s). Expected is ( parameter ).",
            "Element 'route': Missing child element(s). Expected is ( service ).",
            "Element 'route': Missing child element(s). Expected is ( resources ).",
        ];
        // @codingStandardsIgnoreEnd
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * @inheritdoc
     */
    public function testFileSchemaUsingInvalidXml($expectedErrors = null)
    {
        // @codingStandardsIgnoreStart
        $expectedErrors = [
            "Element 'route', attribute 'method': [facet 'enumeration'] The value 'PATCH' is not an element of the set {'GET', 'PUT', 'POST', 'DELETE'}.",
            "Element 'route', attribute 'method': 'PATCH' is not a valid value of the local atomic type.",
            "Element 'service': The attribute 'method' is required but missing.",
            "Element 'data': Missing child element(s). Expected is ( parameter ).",
        ];
        // @codingStandardsIgnoreEnd
        parent::testFileSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'webapi.xml';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/webapi.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid_webapi.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return __DIR__ . '/_files/partial_webapi.xml';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return __DIR__ . '/_files/partial_invalid_webapi.xml';
    }

    /**
     * Returns the name of the XSD file to be used to validate the XSD
     *
     * @return string
     */
    protected function _getXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:module:Magento_Webapi:etc/webapi_merged.xsd');
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:module:Magento_Webapi:etc/webapi.xsd');
    }
}

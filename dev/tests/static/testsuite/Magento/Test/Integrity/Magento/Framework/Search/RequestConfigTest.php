<?php
/**
 * Test search_request.xsd and xml files.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Framework\Search;

class RequestConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request_merged.xsd');
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request.xsd');
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/request/valid.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/request/invalid.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return __DIR__ . '/_files/request/valid_partial.xml';
    }

    /**
     * @param null $expectedErrors
     */
    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array_filter(
            explode(
                "\n",
                "
No match found for key-sequence ['suggested_search_container'] of keyref 'requestQueryReference'.
Element 'queryReference': No match found for key-sequence ['fulltext_search_query4'] of keyref 'queryReference'.
"
            )
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * @param null $expectedErrors
     */
    public function testFileSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array_filter(
            explode(
                "\n",
                "
Element 'dimensions': Missing child element(s). Expected is ( dimension ).
Element 'queryReference': The attribute 'clause' is required but missing.
Element 'queryReference': The attribute 'ref' is required but missing.
Element 'filterReference': The attribute 'clause' is required but missing.
Element 'filterReference': The attribute 'ref' is required but missing.
Element 'filter': The attribute 'field' is required but missing.
Element 'metric', attribute 'type': [facet 'enumeration'] " .
                "The value 'sumasdasd' is not an element of the set {'sum', 'count', 'min', 'max'}.
Element 'metric', attribute 'type': 'sumasdasd' is not a valid value of the local atomic type.
Element 'bucket': Missing child element(s). Expected is one of ( metrics, ranges ).
Element 'request': Missing child element(s). Expected is ( from )."
            )
        );
        parent::testFileSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'search_request.xml';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return __DIR__ . '/_files/request/invalid_partial.xml';
    }

    public function testSchemaUsingValidXml()
    {
        parent::testSchemaUsingValidXml();
    }
}

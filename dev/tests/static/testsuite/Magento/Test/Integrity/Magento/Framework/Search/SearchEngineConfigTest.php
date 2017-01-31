<?php
/**
 * Test search_request.xsd and xml files.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Framework\Search;

class SearchEngineConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
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
        return $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_engine.xsd');
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_engine.xsd');
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/search_engine/valid.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/search_engine/invalid.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return null;
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
Element 'feature': The attribute 'support' is required but missing.
Element 'wrong': This element is not expected. Expected is ( feature ).
Element 'feature': The attribute 'name' is required but missing.
Element 'engine', attribute 'wrong': The attribute 'wrong' is not allowed.
Element 'engine': The attribute 'name' is required but missing.
Element 'feature', attribute 'support': 'wrong' is not a valid value of the atomic type 'xs:boolean'.
"
            )
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'search_engine.xml';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return null;
    }

    public function testSchemaUsingValidXml()
    {
        parent::testSchemaUsingValidXml();
    }
}

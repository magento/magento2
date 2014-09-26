<?php
/**
 * Test search_request.xsd and xml files.
 *
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
namespace Magento\Test\Integrity\Magento\Framework\Search;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/lib/internal/Magento/Framework/Search/etc/search_request_merged.xsd';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return '/lib/internal/Magento/Framework/Search/etc/search_request.xsd';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/valid.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return __DIR__ . '/_files/valid_partial.xml';
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
No match found for key-sequence ['sugegsted_search_container'] of keyref 'requestQueryReference'.
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
Element 'dimensions': Missing child element(s). Expected is ( dimension )
Element 'queryReference': The attribute 'ref' is required but missing.
Element 'filterReference': The attribute 'ref' is required but missing.
Element 'filter': The attribute 'field' is required but missing.
Element 'bucket': Missing child element(s). Expected is ( metrics ).
Element 'metric', attribute 'type': [facet 'enumeration'] " .
                "The value 'sumasdasd' is not an element of the set {'sum', 'count', 'min', 'max'}.
Element 'metric', attribute 'type': 'sumasdasd' is not a valid value of the local atomic type.
Element 'bucket': Missing child element(s). Expected is ( ranges ).
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
        return __DIR__ . '/_files/invalid_partial.xml';
    }

    public function testSchemaUsingValidXml()
    {
        parent::testSchemaUsingValidXml();
    }
}

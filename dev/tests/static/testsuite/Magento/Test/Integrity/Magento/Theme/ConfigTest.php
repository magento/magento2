<?php
/**
 * Test page_layouts.xsd and xml files
 *
 * Fined "page_layouts.xml" files in code tree and validate them.  Also verify schema fails on an invalid xml and
 * passes on a valid xml
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
namespace Magento\Test\Integrity\Magento\Theme;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    public function testFileSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array("Element 'layout': The attribute 'id' is required but missing.");
        parent::testFileSchemaUsingInvalidXml($expectedErrors);
    }

    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = array(
            "Element 'layouts': No match found for key-sequence ['bad_ref'] of keyref 'layout-ref'.",
            "Element 'layout': Missing child element(s). Expected is ( label ).",
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    public function testSchemaUsingPartialXml($expectedErrors = null)
    {
        $expectedErrors = array(
            "Element 'layout': Missing child element(s). Expected is ( label ).",
            "Element 'layout': Missing child element(s). Expected is ( template ).",
            "Element 'layout': Missing child element(s). Expected is ( layout_handle )."
        );
        parent::testSchemaUsingPartialXml($expectedErrors);
    }

    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/app/code/Magento/Theme/etc/page_layouts.xsd';
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/valid_page_layouts.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/invalid_page_layouts.xml';
    }

    /**
     * The location of a single known valid partial xml file
     *
     * @return string
     */
    protected function _getKnownValidPartialXml()
    {
        return __DIR__ . '/_files/valid_page_layouts_partial.xml';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return '/app/code/Magento/Theme/etc/page_layouts_file.xsd';
    }

    /**
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return __DIR__ . '/_files/invalid_page_layouts_partial.xml';
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'page_layouts.xml';
    }
}

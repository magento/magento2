<?php
/**
 * Test indexer.xsd and xml files.
 *
 * Find "indexer.xml" files in code tree and validate them.  Also verify schema fails on an invalid xml and
 * passes on a valid xml.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Indexer;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        return '/app/code/Magento/Indexer/etc/indexer_merged.xsd';
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
     * The location of a single known invalid partial xml file
     *
     * @return string
     */
    protected function _getKnownInvalidPartialXml()
    {
        return __DIR__ . '/_files/invalid_partial.xml';
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return '/app/code/Magento/Indexer/etc/indexer.xsd';
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return 'indexer.xml';
    }

    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        // @codingStandardsIgnoreStart
        $expectedErrors = [
            "Element 'indexer': Duplicate key-sequence ['catalogsearch_fulltext'] in unique identity-constraint 'uniqueViewId'.",
            "Element 'indexer': Duplicate key-sequence ['indexer_0', 'catalogsearch_fulltext'] in unique identity-constraint 'uniqueIndexertId'.",
            "Element 'fields': Missing child element(s). Expected is ( field ).",
            "Element 'fields', attribute 'handler': [facet 'pattern'] The value 'field_handler' is not accepted by the pattern '[a-zA-Z\\\\]+'.",
            "Element 'fields', attribute 'handler': 'field_handler' is not a valid value of the atomic type 'classType'.",
            "Element 'field': Duplicate key-sequence ['visibility'] in unique identity-constraint 'uniqueField'.",
            "Element 'field', attribute 'origin': [facet 'pattern'] The value 'table_name_field_name' is not accepted by the pattern '[a-zA-Z0-9_]+\\.[a-zA-Z0-9_]+'.",
            "Element 'field', attribute 'origin': 'table_name_field_name' is not a valid value of the atomic type 'originType'.",
            "Element 'field': The attribute 'dataType' is required but missing.",
            "Element 'field', attribute '{http://www.w3.org/2001/XMLSchema-instance}type': The QName value 'any' of the xsi:type attribute does not resolve to a type definition.",
            "Element 'field', attribute 'dataType': [facet 'enumeration'] The value 'string' is not an element of the set {'int', 'float', 'varchar'}.",
            "Element 'field', attribute 'dataType': 'string' is not a valid value of the atomic type 'dataType'."
        ];
        // @codingStandardsIgnoreEnd
        $expectedErrors = array_filter(
            explode(
                "\n",
                "
Element 'indexer': Duplicate key-sequence ['catalogsearch_fulltext'] in unique identity-constraint 'uniqueViewId'.
Element 'indexer': Duplicate key-sequence ['indexer_0', 'catalogsearch_fulltext'] in unique identity-constraint" .
                " 'uniqueIndexertId'.
Element 'fieldset': Missing child element(s). Expected is ( field ).
Element 'field', attribute 'handler': [facet 'pattern'] " .
                "The value 'Magento\\Framework\\Search\\Index\\Field\\Handler\\Class' is not accepted by the pattern " .
                "'[a-zA-Z0-9_]+'.
Element 'field', attribute 'handler': 'Magento\\Framework\\Search\\Index\\Field\\Handler\\Class' is not a valid " .
                "value of the atomic type 'nameType'.
Element 'field', attribute 'handler': Warning: No precomputed value available, the value was either invalid or " .
                "something strange happend.
Element 'field': Duplicate key-sequence ['visibility'] in unique identity-constraint 'uniqueField'.
Element 'field': No match found for key-sequence ['tableSource'] of keyref 'sourceReference'.
Element 'field': No match found for key-sequence ['handler'] of keyref 'handlerReference'.
Element 'field': The attribute 'dataType' is required but missing.
Element 'field', attribute '{http://www.w3.org/2001/XMLSchema-instance}type': The QName value 'any'" .
                " of the xsi:type attribute does not resolve to a type definition.
Element 'field', attribute 'dataType': [facet 'enumeration'] The value 'string' is not an element" .
                " of the set {'int', 'float', 'varchar'}.
Element 'field', attribute 'dataType': 'string' is not a valid value of the atomic type 'dataType'.
Element 'field': The attribute 'dataType' is required but missing."
            )
        );
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }
}

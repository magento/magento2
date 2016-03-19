<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
{
    public function testSchemaUsingInvalidXml($expectedErrors = null)
    {
        $expectedErrors = [
            "Element 'code': [facet 'pattern'] The value 'e_GB' is not accepted by the pattern",
            "Element 'code': 'e_GB' is not a valid value of the atomic type 'codeType'",
            "Element 'vendor': [facet 'pattern'] The value 'Magento' is not accepted by the pattern",
            "Element 'vendor': 'Magento' is not a valid value of the atomic type",
            "Element 'sort_odrer': This element is not expected. Expected is",
        ];
        parent::testSchemaUsingInvalidXml($expectedErrors);
    }

    /**
     * Returns the name of the XSD file to be used to validate the XML
     *
     * @return string
     */
    protected function _getXsd()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        return $urnResolver->getRealPath('urn:magento:framework:App/Language/package.xsd');
    }

    /**
     * The location of a single valid complete xml file
     *
     * @return string
     */
    protected function _getKnownValidXml()
    {
        return __DIR__ . '/_files/known_valid.xml';
    }

    /**
     * The location of a single known invalid complete xml file
     *
     * @return string
     */
    protected function _getKnownInvalidXml()
    {
        return __DIR__ . '/_files/known_invalid.xml';
    }

    /**
     * {@inheritdoc}
     */
    protected function _getKnownValidPartialXml()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getFileXsd()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getKnownInvalidPartialXml()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getXmlName()
    {
        return;
    }
}

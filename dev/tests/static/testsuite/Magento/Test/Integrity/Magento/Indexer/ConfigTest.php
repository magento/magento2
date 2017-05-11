<?php
/**
 * Test indexer.xsd and xml files.
 *
 * Find "indexer.xml" files in code tree and validate them.  Also verify schema fails on an invalid xml and
 * passes on a valid xml.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Indexer;

class ConfigTest extends \Magento\TestFramework\Integrity\AbstractConfig
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
        return $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd');
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
        return null;
    }

    /**
     * Returns the name of the XSD file to be used to validate partial XML
     *
     * @return string
     */
    protected function _getFileXsd()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd');
    }

    /**
     * Returns the name of the xml files to validate
     *
     * @return string
     */
    protected function _getXmlName()
    {
        return '/lib/internal/Magento/Framework/Indexer/etc/indexer.xml';
    }
}

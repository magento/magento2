<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Config;

/**
 * This is system class that provides .xsd file for validation XML schema.
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config.
     *
     * @var string
     */
    protected $_schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files.
     *
     * @var string
     */
    protected $_perFileSchema = null;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param string                                    $schemaUrn
     */
    public function __construct(
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver,
        $schemaUrn = 'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd'
    ) {
        $this->_schema = $urnResolver->getRealPath($schemaUrn);
        $this->_perFileSchema = $this->_schema;
    }

    /**
     * Get path to merged config schema.
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to pre file validation schema.
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Communication\Config\Reader\XmlReader;

/**
 * Schema locator for Publishers
 * @since 2.1.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.1.0
     */
    protected $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     * @since 2.1.0
     */
    protected $perFileSchema;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->schema = $urnResolver->getRealPath('urn:magento:framework:Communication/etc/communication.xsd');
        $this->perFileSchema = $urnResolver->getRealPath('urn:magento:framework:Communication/etc/communication.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.1.0
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.1.0
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}

<?php
/**
 * Locator for fieldset XSD schemas.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Copy\Config;

use Magento\Framework\Config\Dom\UrnResolver;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema;

    /**
     * @param UrnResolver $urnResolver
     * @param string $schema
     * @param string $perFileSchema
     */
    public function __construct(UrnResolver $urnResolver, $schema, $perFileSchema)
    {
        $this->_schema = $urnResolver->getRealPath($schema);
        $this->_perFileSchema = $urnResolver->getRealPath($perFileSchema);
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}

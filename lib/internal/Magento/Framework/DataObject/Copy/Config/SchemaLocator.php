<?php
/**
 * Locator for fieldset XSD schemas.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Copy\Config;

use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class \Magento\Framework\DataObject\Copy\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.0.0
     */
    protected $_schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     * @since 2.0.0
     */
    protected $_perFileSchema;

    /**
     * @param UrnResolver $urnResolver
     * @param string $schema
     * @param string $perFileSchema
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}

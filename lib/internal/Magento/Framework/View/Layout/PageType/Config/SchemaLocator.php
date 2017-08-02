<?php
/**
 * Locator for page_types XSD schemas.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType\Config;

/**
 * Class \Magento\Framework\View\Layout\PageType\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     * @since 2.0.0
     */
    protected $schema;

    /**
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->schema = $urnResolver->getRealPath('urn:magento:framework:View/Layout/etc/page_types.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->schema;
    }
}

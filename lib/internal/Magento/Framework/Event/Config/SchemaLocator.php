<?php
/**
 * Event observers configuration schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Config;

/**
 * Class \Magento\Framework\Event\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     * @since 2.0.0
     */
    protected $urnResolver;

    /**
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd');
    }
}

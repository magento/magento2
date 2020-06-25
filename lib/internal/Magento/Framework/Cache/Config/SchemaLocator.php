<?php
/**
 * Cache configuration schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Config;

/**
 * Cache configuration schema locator
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Cache/etc/cache.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Cache/etc/cache.xsd');
    }
}

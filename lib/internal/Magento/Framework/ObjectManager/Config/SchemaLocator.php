<?php
/**
 * Object manager configuration schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config;

/**
 * Class \Magento\Framework\ObjectManager\Config\SchemaLocator
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
    public function __construct()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:ObjectManager/etc/config.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return null
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

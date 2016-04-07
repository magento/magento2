<?php
/**
 * Object manager configuration schema locator
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    public function __construct()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:ObjectManager/etc/config.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

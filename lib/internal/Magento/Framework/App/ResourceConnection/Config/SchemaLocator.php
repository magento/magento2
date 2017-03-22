<?php
/**
 * Resource configuration schema locator
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection\Config;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:App/etc/resources.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->getPerFileSchema();
    }
}

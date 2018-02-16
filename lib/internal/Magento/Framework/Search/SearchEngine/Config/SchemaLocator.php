<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\SearchEngine\Config;

use Magento\Framework\Config\SchemaLocatorInterface;

class SchemaLocator implements SchemaLocatorInterface
{
    const SEARCH_ENGINE_XSD_PATH = 'urn:magento:framework:Search/etc/search_engine.xsd';

    /**
     * URN resolver
     *
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * Constructor
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
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath(self::SEARCH_ENGINE_XSD_PATH);
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath(self::SEARCH_ENGINE_XSD_PATH);
    }
}

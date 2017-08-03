<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * Class \Magento\Framework\Indexer\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements SchemaLocatorInterface
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
     * @return string|null
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd');
    }
}

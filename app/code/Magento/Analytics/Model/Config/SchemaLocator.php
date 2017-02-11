<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * Specifies schema for xml validation.
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Schema URN reference.
     *
     * @var string
     */
    private $schema = 'urn:magento:module:Magento_Analytics:etc/analytics.xsd';

    /**
     * @var UrnResolver
     */
    private $urnResolver;

    /**
     * @param UrnResolver $urnResolver
     */
    public function __construct(
        UrnResolver $urnResolver
    ) {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema.
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath($this->schema);
    }

    /**
     * Get path to pre file validation schema.
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

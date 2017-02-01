<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * Class SchemaLocator
 *
 * Specifies schema for xml validation
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $realPath = 'urn:magento:module:Magento_Analytics:etc/reports.xsd';

    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    private $urnResolver;

    /**
     * SchemaLocator constructor.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver
    ) {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath($this->realPath);
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

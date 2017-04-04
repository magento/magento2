<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\Config;

use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * A reports configuration schema locator.
 *
 * Specifies the XSD schema for validation of reports configuration stored in XML format.
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
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(
        \Magento\Framework\Config\Dom\UrnResolver $urnResolver
    ) {
        $this->urnResolver = $urnResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath($this->realPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return null;
    }
}

<?php
/**
 * Locator for page layouts XSD schemas.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class \Magento\Theme\Model\Layout\Config\SchemaLocator
 *
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * @param UrnResolver $urnResolver
     */
    public function __construct(UrnResolver $urnResolver)
    {
        $this->_schema = $urnResolver->getRealPath('urn:magento:framework:View/PageLayout/etc/layouts.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}

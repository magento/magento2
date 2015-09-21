<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

/**
 * Schema locator for Publishers
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $perFileSchema;

    /**
     * Initialize dependencies.
     */
    public function __construct()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->schema = $urnResolver->getRealPath('urn:magento:framework:Amqp/etc/queue_merged.xsd');
        $this->perFileSchema = $urnResolver->getRealPath('urn:magento:framework:Amqp/etc/queue.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}

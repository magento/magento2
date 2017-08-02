<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Xml;

/**
 * Schema locator for etc/queue_publisher.xml
 * @since 2.2.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.2.0
     */
    protected $schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     * @since 2.2.0
     */
    protected $perFileSchema;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->schema = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
        $this->perFileSchema = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.2.0
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.2.0
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\AclResource\Config;

/**
 * ACL resources configuration schema locator
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urnResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl_merged.xsd');
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl.xsd');
    }
}

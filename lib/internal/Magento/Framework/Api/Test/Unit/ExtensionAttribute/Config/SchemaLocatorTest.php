<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\ExtensionAttribute\Config;

/**
 * Test for \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator
 */
class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Api/etc/extension_attributes.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Api/etc/extension_attributes.xsd')
            );
        $this->model = new \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator($urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Api/etc/extension_attributes.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Api/etc/extension_attributes.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}

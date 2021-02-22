<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Request\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Cache\Config\SchemaLocator */
    protected $schemaLocator;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $this->urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $this->schemaLocator = new \Magento\Framework\Search\Request\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Search/etc/search_request_merged.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request_merged.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request_merged.xsd'),
            $this->schemaLocator->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Search/etc/search_request.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Search/etc/search_request.xsd'),
            $this->schemaLocator->getPerFileSchema()
        );
    }
}

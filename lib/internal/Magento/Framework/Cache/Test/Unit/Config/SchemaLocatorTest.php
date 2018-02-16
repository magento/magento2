<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Cache\Config\SchemaLocator */
    protected $schemaLocator;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $this->urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
        $this->schemaLocator = new \Magento\Framework\Cache\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Cache/etc/cache.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Cache/etc/cache.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Cache/etc/cache.xsd'),
            $this->schemaLocator->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(null, $this->schemaLocator->getPerFileSchema());
    }
}

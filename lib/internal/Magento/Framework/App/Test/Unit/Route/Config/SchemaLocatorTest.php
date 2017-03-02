<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Route\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Route\Config\SchemaLocator
     */
    protected $config;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $this->urnResolverMock = $this->getMock(\Magento\Framework\Config\Dom\UrnResolver::class, [], [], '', false);
        $this->config = new \Magento\Framework\App\Route\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/etc/routes_merged.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes_merged.xsd')
            );
        $this->assertContains(
            $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes_merged.xsd'),
            $this->config->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/etc/routes.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes.xsd')
            );
        $this->assertContains(
            $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes.xsd'),
            $this->config->getPerFileSchema()
        );
    }
}

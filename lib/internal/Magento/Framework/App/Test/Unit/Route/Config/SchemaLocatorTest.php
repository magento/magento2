<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Route\Config;

use Magento\Framework\App\Route\Config\SchemaLocator;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $config;

    /** @var UrnResolver */
    protected $urnResolver;

    /** @var UrnResolver */
    protected $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        /** @var UrnResolver $urnResolverMock */
        $this->urnResolverMock = $this->createMock(UrnResolver::class);
        $this->config = new SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/etc/routes_merged.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes_merged.xsd')
            );
        $this->assertStringContainsString(
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
        $this->assertStringContainsString(
            $this->urnResolver->getRealPath('urn:magento:framework:App/etc/routes.xsd'),
            $this->config->getPerFileSchema()
        );
    }
}

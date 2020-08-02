<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Request\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Search\Request\Config\SchemaLocator as SchemaLocatorConfig;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /** @var \Magento\Framework\Cache\Config\SchemaLocator */
    protected $schemaLocator;

    /** @var UrnResolver */
    protected $urnResolver;

    /** @var UrnResolver */
    protected $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        /** @var UrnResolver $urnResolverMock */
        $this->urnResolverMock = $this->createMock(UrnResolver::class);
        $this->schemaLocator = new SchemaLocatorConfig($this->urnResolverMock);
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

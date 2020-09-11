<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Config;

use Magento\Framework\Cache\Config\SchemaLocator;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /** @var SchemaLocator */
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
        $this->schemaLocator = new SchemaLocator($this->urnResolverMock);
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
        $this->assertNull($this->schemaLocator->getPerFileSchema());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\AclResource\Config;

use Magento\Framework\Acl\AclResource\Config\SchemaLocator;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    public function testGetSchema()
    {
        $urnResolver = new UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Acl/etc/acl_merged.xsd')
            ->willReturn($urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl_merged.xsd'));
        $schemaLocator = new SchemaLocator($urnResolverMock);
        $this->assertEquals(
            $urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl_merged.xsd'),
            $schemaLocator->getSchema()
        );
    }
}

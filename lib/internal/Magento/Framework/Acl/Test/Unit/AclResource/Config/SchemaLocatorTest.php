<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\AclResource\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSchema()
    {
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Acl/etc/acl.xsd')
            ->willReturn($urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl.xsd'));
        $schemaLocator = new \Magento\Framework\Acl\AclResource\Config\SchemaLocator($urnResolverMock);
        $this->assertEquals(
            $urnResolver->getRealPath('urn:magento:framework:Acl/etc/acl.xsd'),
            $schemaLocator->getSchema()
        );
    }
}

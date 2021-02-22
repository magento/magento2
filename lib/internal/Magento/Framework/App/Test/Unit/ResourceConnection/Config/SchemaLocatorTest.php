<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    protected function setUp(): void
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/etc/resources.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:App/etc/resources.xsd')
            );
        $this->model = new \Magento\Framework\App\ResourceConnection\Config\SchemaLocator($urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:App/etc/resources.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:App/etc/resources.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}

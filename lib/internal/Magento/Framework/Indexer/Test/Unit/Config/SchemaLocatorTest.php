<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->getMock(\Magento\Framework\Config\Dom\UrnResolver::class, [], [], '', false);
        $this->model = new \Magento\Framework\Indexer\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Indexer/etc/indexer_merged.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Indexer/etc/indexer.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}

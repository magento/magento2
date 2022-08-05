<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Test\Unit\Config;

use Magento\Framework\App\ResourceConnection\Config\SchemaLocator as SchemaLocatorConfig;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{

    /**
     * @var SchemaLocatorConfig
     */
    protected $model;

    /** @var UrnResolver */
    protected $urnResolver;

    /** @var UrnResolver */
    protected $urnResolverMock;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        $this->urnResolverMock = $this->createMock(UrnResolver::class);
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

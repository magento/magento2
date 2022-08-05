<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

use Magento\Framework\App\ResourceConnection\Config\SchemaLocator;
use Magento\Framework\Config\Dom\UrnResolver;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{

    /**
     * @var SchemaLocator
     */
    protected $model;

    /** @var UrnResolver */
    protected $urnResolver;

    protected function setUp(): void
    {
        $this->urnResolver = new UrnResolver();
        /** @var UrnResolver $urnResolverMock */
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:App/etc/resources.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:App/etc/resources.xsd')
            );
        $this->model = new SchemaLocator($urnResolverMock);
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

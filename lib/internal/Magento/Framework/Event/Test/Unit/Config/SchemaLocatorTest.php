<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit\Config;

use Magento\Framework\App\ResourceConnection\Config\SchemaLocator as SchemaLocatorConfigResourceModel;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Event\Config\SchemaLocator;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocatorConfigResourceModel
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
        $this->model = new SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Event/etc/events.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Event/etc/events.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Event/etc/events.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\ObjectManager\Config\SchemaLocator as SchemaLocatorConfig;
use PHPUnit\Framework\TestCase;

class SchemaLocatorTest extends TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
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
        $this->model = new SchemaLocatorConfig($this->urnResolverMock);
        $property = new \ReflectionProperty($this->model, 'urnResolver');
        $property->setAccessible(true);
        $property->setValue($this->model, $this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:ObjectManager/etc/config.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:ObjectManager/etc/config.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:ObjectManager/etc/config.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->assertNull($this->model->getPerFileSchema());
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Config;

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
        $this->model = new \Magento\Framework\ObjectManager\Config\SchemaLocator($this->urnResolverMock);
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
        $this->assertEquals(
            null,
            $this->model->getPerFileSchema()
        );
    }
}

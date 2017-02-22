<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolver;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
        $urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
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

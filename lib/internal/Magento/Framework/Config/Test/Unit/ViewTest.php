<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\View
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
        $this->model = new \Magento\Framework\Config\View(
            [
                file_get_contents(__DIR__ . '/_files/view_one.xml'),
                file_get_contents(__DIR__ . '/_files/view_two.xml'),
            ],
            $this->urnResolverMock
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructException()
    {
        new \Magento\Framework\Config\View([], $this->urnResolverMock);
    }

    public function testGetSchemaFile()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Config/etc/view.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/view.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Config/etc/view.xsd'),
            $this->model->getSchemaFile()
        );
        $this->assertFileExists($this->model->getSchemaFile());
    }

    public function testGetVars()
    {
        $this->assertEquals(['one' => 'Value One', 'two' => 'Value Two'], $this->model->getVars('Two'));
    }

    public function testGetVarValue()
    {
        $this->assertFalse($this->model->getVarValue('Unknown', 'nonexisting'));
        $this->assertEquals('Value One', $this->model->getVarValue('Two', 'one'));
        $this->assertEquals('Value Two', $this->model->getVarValue('Two', 'two'));
        $this->assertEquals('Value Three', $this->model->getVarValue('Three', 'three'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testInvalidXml()
    {
        new \Magento\Framework\Config\View(
            [file_get_contents(__DIR__ . '/_files/view_invalid.xml')],
            $this->urnResolverMock
        );
    }

    public function testGetExcludedFiles()
    {
        $this->assertEquals(2, count($this->model->getExcludedFiles()));
    }

    public function testGetExcludedDir()
    {
        $this->assertEquals(1, count($this->model->getExcludedDir()));
    }
}

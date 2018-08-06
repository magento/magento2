<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Dom;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Component\ComponentRegistrar;

class UrnResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrnResolver
     */
    protected $urnResolver;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urnResolver = $this->objectManagerHelper->getObject(\Magento\Framework\Config\Dom\UrnResolver::class);
    }

    public function testGetRealPathNoUrn()
    {
        $xsdPath = '../../testPath/test.xsd';
        $result = $this->urnResolver->getRealPath($xsdPath);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    public function testGetRealPathWithFrameworkUrn()
    {
        $xsdUrn = 'urn:magento:framework:Config/Test/Unit/_files/sample.xsd';
        $xsdPath = realpath(dirname(__DIR__)) . '/_files/sample.xsd';
        $result = $this->urnResolver->getRealPath($xsdUrn);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    public function testGetRealPathWithModuleUrn()
    {
        $xsdUrn = 'urn:magento:module:Magento_Customer:etc/address_formats.xsd';
        $componentRegistrar = new ComponentRegistrar();
                $xsdPath = $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Customer')
                    . '/etc/address_formats.xsd';

        $result = $this->urnResolver->getRealPath($xsdUrn);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Unsupported format of schema location: 'urn:magento:test:test:etc/test_test.xsd'
     */
    public function testGetRealPathWrongSection()
    {
        $xsdUrn = 'urn:magento:test:test:etc/test_test.xsd';
        $this->urnResolver->getRealPath($xsdUrn);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Could not locate schema: 'urn:magento:module:Magento_Test:test.xsd' at '/test.xsd'
     */
    public function testGetRealPathWrongModule()
    {
        $xsdUrn = 'urn:magento:module:Magento_Test:test.xsd';
        $this->urnResolver->getRealPath($xsdUrn);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit\Dom;

use \Magento\Framework\Config\Dom\UrnResolver;
use \Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\Module\PackageInfo;

class UrnResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrnResolver
     */
    protected $urnResolver;

    /**
     * @var ComponentRegistrarInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registrarMock;

    /**
     * @var PackageInfo | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $packageInfoMock;

    /**
     * @var PackageInfoFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $packageInfoFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    public function setUp()
    {
        $this->registrarMock = $this->getMockBuilder('Magento\Framework\Component\ComponentRegistrarInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getPaths'])
            ->getMock();

        $this->packageInfoMock = $this->getMockBuilder('Magento\Framework\Module\PackageInfo')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleName'])
            ->getMock();
        $this->packageInfoFactoryMock = $this->getMockBuilder('Magento\Framework\Module\PackageInfoFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->packageInfoFactoryMock->expects($this->any())->method('create')->willReturn($this->packageInfoMock);

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'componentRegistrar' => $this->registrarMock,
            'packageInfoFactory' => $this->packageInfoFactoryMock,
        ];
        $this->urnResolver = $this->objectManagerHelper->getObject(
            'Magento\Framework\Config\Dom\UrnResolver',
            $arguments
        );
    }

    public function testGetRealPathNoUrn()
    {
        $xsdPath = '../../testPath/test.xsd';
        $result = $this->urnResolver->getRealPath($xsdPath);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    public function testGetRealPathWithFrameworkUrn()
    {
        $xsdUrn = 'urn:magento:library:framework:Config/Test/Unit/_files/sample.xsd';
        $xsdPath = realpath(dirname(__DIR__)) . '/_files/sample.xsd';
        $registrarResult = realpath(dirname(dirname(dirname(dirname(__DIR__)))));
        $this->registrarMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::LIBRARY, 'magento/framework')
            ->willReturn($registrarResult);

        $result = $this->urnResolver->getRealPath($xsdUrn);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    public function testGetRealPathWithModuleUrn()
    {
        $xsdUrn = 'urn:magento:module:customer:etc/address_formats.xsd';
        $xsdPath = realpath(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))))))
            . '/app/code/Magento/Customer/etc/address_formats.xsd';
        $this->packageInfoMock->expects($this->once())->method('getModuleName')->willReturn('Magento_Customer');
        $registrarResult = realpath(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))))))
            . '/app/code/Magento/Customer';
        $this->registrarMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Magento_Customer')
            ->willReturn($registrarResult);

        $result = $this->urnResolver->getRealPath($xsdUrn);
        $this->assertSame($xsdPath, $result, 'XSD paths does not match.');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unsupported format of schema location: urn:magento:test:test:etc/address_formats.xsd
     */
    public function testGetRealPathWrongSection()
    {
        $xsdUrn = 'urn:magento:test:test:etc/address_formats.xsd';
        $this->urnResolver->getRealPath($xsdUrn);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Could not locate schema: 'urn:magento:module:test:testfile.xsd' at '/testfile.xsd'
     */
    public function testGetRealPathWrongModule()
    {
        $xsdUrn = 'urn:magento:module:test:testfile.xsd';
        $this->packageInfoMock->expects($this->once())->method('getModuleName')->willReturn('Magento_Test');
        $this->registrarMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Magento_Test')
            ->willReturn('');

        $this->urnResolver->getRealPath($xsdUrn);
    }
}

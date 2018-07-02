<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer\Test\Unit;

use Composer\Composer;
use Composer\Package\Locker;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ComposerInformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerMock;

    /**
     * @var Locker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockerMock;

    /**
     * @var \Composer\Repository\RepositoryInterface|\PHPUnit\Framework_MockObject_Builder_InvocationMocker:
     */
    private $lockerRepositoryMock;

    /**
     * @var \Composer\Package\CompletePackageInterface|\PHPUnit\Framework_MockObject_Builder_InvocationMocker:
     */
    private $packageMock;

    public function setUp()
    {
        $this->composerMock = $this->getMockBuilder(Composer::class)->disableOriginalConstructor()->getMock();
        $this->lockerMock = $this->getMockBuilder(Locker::class)->disableOriginalConstructor()->getMock();
        $this->lockerRepositoryMock = $this->getMockForAbstractClass(\Composer\Repository\RepositoryInterface::class);
        $this->packageMock = $this->getMockForAbstractClass(\Composer\Package\CompletePackageInterface::class);
        $this->lockerMock->method('getLockedRepository')->willReturn($this->lockerRepositoryMock);
        $this->packageMock->method('getType')->willReturn('metapackage');
        $this->packageMock->method('getPrettyName')->willReturn('magento/product-test-package-name');
        $this->packageMock->method('getName')->willReturn('magento/product-test-package-name');
        $this->packageMock->method('getPrettyVersion')->willReturn('123.456.789');
        $this->lockerRepositoryMock->method('getPackages')->willReturn([$this->packageMock]);

        $objectManager = new ObjectManager($this);
        $this->composerInformation = $objectManager->getObject(
            \Magento\Framework\Composer\ComposerInformation::class,
            [
                'composer' => $this->composerMock,
                'locker' => $this->lockerMock
            ]
        );
    }

    public function testGetSystemPackages()
    {
        $expected = [
            'magento/product-test-package-name' => [
                'name'    => 'magento/product-test-package-name',
                'type'    => 'metapackage',
                'version' => '123.456.789'
            ]
        ];
        $this->assertEquals($expected, $this->composerInformation->getSystemPackages());
    }

    public function testGetRootPackage()
    {
        $rootPackageMock = $this->getMockForAbstractClass(\Composer\Package\RootPackageInterface::class);
        $this->composerMock->expects($this->once())->method('getPackage')->willReturn($rootPackageMock);
        $this->assertEquals($rootPackageMock, $this->composerInformation->getRootPackage());
    }

    /**
     * @param string $packageName
     * @param boolean $expected
     * @dataProvider isMagentoRootDataProvider
     */
    public function testIsMagentoRoot($packageName, $expected)
    {
        $rootPackageMock = $this->getMockForAbstractClass(\Composer\Package\RootPackageInterface::class);
        $this->composerMock->expects($this->once())->method('getPackage')->willReturn($rootPackageMock);
        $rootPackageMock->method('getName')->willReturn($packageName);
        $this->assertEquals($expected, $this->composerInformation->isMagentoRoot());
    }

    /**
     * @return array
     */
    public function isMagentoRootDataProvider()
    {
        return [
            ['magento/magento2ce', true],
            ['magento/magento2ee', true],
            ['namespace/package', false],
        ];
    }
}

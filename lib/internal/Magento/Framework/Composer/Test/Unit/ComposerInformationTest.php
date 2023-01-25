<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Composer\Test\Unit;

use Composer\Composer;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use Composer\Repository\LockArrayRepository;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComposerInformationTest extends TestCase
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var Composer|MockObject
     */
    private $composerMock;

    /**
     * @var Locker|MockObject
     */
    private $lockerMock;

    /**
     * @var LockArrayRepository|InvocationMocker
     */
    private $lockerRepositoryMock;

    /**
     * @var CompletePackageInterface|InvocationMocker
     */
    private $packageMock;

    protected function setUp(): void
    {
        $this->composerMock = $this->getMockBuilder(Composer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockerMock = $this->getMockBuilder(Locker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->lockerRepositoryMock = $this->getMockBuilder(LockArrayRepository::class)
            ->setMethods(['getLockedRepository','getPackages'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageMock = $this->getMockForAbstractClass(CompletePackageInterface::class);
        $this->lockerMock->method('getLockedRepository')->willReturn($this->lockerRepositoryMock);
        $this->packageMock->method('getType')->willReturn('metapackage');
        $this->packageMock->method('getPrettyName')->willReturn('magento/product-test-package-name-edition');
        $this->packageMock->method('getName')->willReturn('magento/product-test-package-name-edition');
        $this->packageMock->method('getPrettyVersion')->willReturn('123.456.789');
        $this->lockerRepositoryMock->method('getPackages')->willReturn([$this->packageMock]);

        $objectManager = new ObjectManager($this);
        $this->composerInformation = $objectManager->getObject(
            ComposerInformation::class,
            [
                'composer' => $this->composerMock,
                'locker' => $this->lockerMock
            ]
        );
    }

    public function testGetSystemPackages()
    {
        $expected = [
            'magento/product-test-package-name-edition' => [
                'name'    => 'magento/product-test-package-name-edition',
                'type'    => 'metapackage',
                'version' => '123.456.789'
            ]
        ];
        $this->assertEquals($expected, $this->composerInformation->getSystemPackages());
    }

    public function testGetRootPackage()
    {
        $rootPackageMock = $this->getMockForAbstractClass(RootPackageInterface::class);
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
        $rootPackageMock = $this->getMockForAbstractClass(RootPackageInterface::class);
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

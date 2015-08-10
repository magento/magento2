<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\SystemPackage;
use Magento\Composer\InfoCommand;

class SystemPackageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Composer\InfoCommand
     */
    private $infoCommand;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SystemPackage;
     */
    private $systemPackage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Composer\Repository\ArrayRepository
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Composer\Package\Locker
     */
    private $locker;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|/Magento\Composer\MagentoComposerApplication
     */
    private $magentoComposerApp;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\MagentoComposerApplicationFactory
     */
    private $composerAppFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Composer\Composer
     */
    private $composer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;


    public function setUp()
    {
        $this->composerAppFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );

        $this->infoCommand = $this->getMock(
            '\Magento\Composer\InfoCommand',
            [],
            [],
            '',
            false
        );

        $this->magentoComposerApp = $this->getMock('Magento\Composer\MagentoComposerApplication', [], [], '', false);
        $this->locker = $this->getMock('Composer\Package\Locker', [], [], '', false);
        $this->repository = $this->getMock('Composer\Repository\ArrayRepository', [], [], '', false);
        $this->composer = $this->getMock('Composer\Composer', [], [], '', false);
        $this->composerInformation = $this->getMock(
            'Magento\Framework\Composer\ComposerInformation',
            [],
            [],
            '',
            false
        );
    }

    public function testGetPackageVersions()
    {
        $package = $this->getMock('\Composer\Package\Package', [], [], '', false);
        $package->expects($this->once())->method('getName')->willReturn('magento/product-community-edition');
        $this->composerInformation->expects($this->once())->method('isSystemPackage')->willReturn(true);
        $this->repository->expects($this->once())->method('getPackages')->willReturn([$package]);
        $this->locker->expects($this->once())->method('getLockedRepository')->willReturn($this->repository);

        $this->composer->expects($this->once())->method('getLocker')->willReturn($this->locker);
        $this->magentoComposerApp->expects($this->once())->method('createComposer')->willReturn($this->composer);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->composerAppFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->magentoComposerApp);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);

        $expected = [
            'package' => 'magento/product-community-edition',
            'versions' => [
                ['id' => '1.2.0', 'name' => 'Version 1.2.0 (latest)'],
                ['id' => '1.1.0', 'name' => 'Version 1.1.0'],
                ['id' => '1.0.0', 'name' => 'Version 1.0.0 (current)']
            ]
        ];

        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with('magento/product-community-edition')
            ->willReturn(
                [
                    'name' => 'magento/product-community-edition',
                    'descrip.' => 'eCommerce Platform for Growth (Community Edition)',
                    'keywords' => '',
                    'versions' => '1.2.0, 1.1.0, * 1.0.0',
                    'type' => 'metapackage',
                    'license' => 'OSL-3.0, AFL-3.0',
                    'source' => '[]',
                    'names' => 'magento/product-community-edition',
                    'current_version' => '1.0.0',
                    'available_versions' => [
                        1 => '1.2.0',
                        2 => '1.1.0',
                        3 => '1.0.0',
                    ],
                    'new_versions' => [
                        '1.2.0',
                        '1.1.0'
                    ]
                ]
            );

        $this->assertEquals($expected, $this->systemPackage->getPackageVersions());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage System package not found
     */
    public function testGetPackageVersionsFailed()
    {
        $package = $this->getMock('\Composer\Package\Package', [], [], '', false);

        $package->expects($this->once())->method('getName')->willReturn('magento/product-community-edition');
        $this->composerInformation->expects($this->once())->method('isSystemPackage')->willReturn(true);

        $this->repository->expects($this->once())->method('getPackages')->willReturn([$package]);

        $this->locker->expects($this->once())->method('getLockedRepository')->willReturn($this->repository);

        $this->composer->expects($this->once())->method('getLocker')->willReturn($this->locker);
        $this->magentoComposerApp->expects($this->once())->method('createComposer')->willReturn($this->composer);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->composerAppFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->magentoComposerApp);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);

        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with('magento/product-community-edition')
            ->willReturn(false);

        $this->systemPackage->getPackageVersions();
    }
}

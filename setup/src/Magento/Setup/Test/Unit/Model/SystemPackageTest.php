<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\SystemPackage;

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
    }

    public function testGetPackageVersions()
    {
        $package = $this->getMock('\Composer\Package\Package', [], [], '', false);

        $package->expects($this->once())->method('getName')->willReturn('magento/product-community-edition');

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

        $this->systemPackage = new SystemPackage($this->composerAppFactory);

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
                array (
                    'name' => 'magento/product-community-edition',
                    'descrip.' => 'eCommerce Platform for Growth (Community Edition)',
                    'keywords' => '',
                    'versions' => 'dev-master, 1.2.0, * 1.1.0, 1.0.0, 1.0.0-beta, 0.74.0-beta16, 0.74.0-beta15, 0.74.0-beta14, 0.74.0-beta13, 0.74.0-beta12, 0.74.0-beta11, 0.74.0-beta10, 0.74.0-beta9, 0.74.0-beta8, 0.74.0-beta7, 0.74.0-beta6, 0.74.0-beta5, 0.74.0-beta4, 0.74.0-beta3, 0.74.0-beta2, 0.74.0-beta1, 0.42.0-beta11, 0.42.0-beta10, 0.42.0-beta9, 0.42.0-beta8, 0.42.0-beta7, 0.42.0-beta6, 0.42.0-beta5, 0.42.0-beta4, 0.42.0-beta3, 0.42.0-beta2, 0.42.0-beta1, 0.1.0-alpha108, 0.1.0-alpha107, 0.1.0-alpha106, 0.1.0-alpha105, 0.1.0-alpha104, 0.1.0-alpha103, 0.1.0-alpha102, 0.1.0-alpha101, 0.1.0-alpha100, 0.1.0-alpha99, 0.1.0-alpha98, 0.1.0-alpha97, 0.1.0-alpha96, 0.1.0-alpha95, 0.1.0-alpha94, 0.1.0-alpha93, 0.1.0-alpha92, 0.1.0-alpha91, 0.1.0-alpha90, 0.1.0-alpha89',
                    'type' => 'metapackage',
                    'license' => 'OSL-3.0, AFL-3.0',
                    'source' => '[]',
                    'dist' => '[zip] /Users/igavryshko/projects/aProject/pr1/packages_test/magento_product-community-edition-1.1.0.zip',
                    'names' => 'magento/product-community-edition',
                    'current_version' => '1.0.0',
                    'available_versions' =>
                        array (
                            0 => 'dev-master',
                            1 => '1.2.0',
                            2 => '1.1.0',
                            3 => '1.0.0',
                        ),
                )
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

        $this->systemPackage = new SystemPackage($this->composerAppFactory);



        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with('magento/product-community-edition')
            ->willReturn(false);

        $this->systemPackage->getPackageVersions();
    }
}


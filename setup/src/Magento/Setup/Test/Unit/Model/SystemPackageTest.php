<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Composer\InfoCommand;
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

    /**
     * @var array
     */
    private $expectedPackages = [
        [
            'id' => '1.2.0',
            'name' => 'Version 1.2.0 EE (latest)',
            'package' => SystemPackage::EDITION_ENTERPRISE,
            'stable' => true,
            'current' => false,
        ],
        [
            'id' => '1.2.0',
            'name' => 'Version 1.2.0 CE (latest)',
            'package' => SystemPackage::EDITION_COMMUNITY,
            'stable' => true,
            'current' => false,
        ],
        [
            'id' => '1.1.0',
            'name' => 'Version 1.1.0 EE',
            'package' => SystemPackage::EDITION_ENTERPRISE,
            'stable' => true,
            'current' => false,
        ],
        [
            'id' => '1.1.0',
            'name' => 'Version 1.1.0 CE',
            'package' => SystemPackage::EDITION_COMMUNITY,
            'stable' => true,
            'current' => false,
        ],
        [
            'id' => '1.1.0-RC1',
            'name' => 'Version 1.1.0-RC1 EE (unstable version)',
            'package' => SystemPackage::EDITION_ENTERPRISE,
            'stable' => false,
            'current' => false,
        ],
        [
            'id' => '1.1.0-RC1',
            'name' => 'Version 1.1.0-RC1 CE (unstable version)',
            'package' => SystemPackage::EDITION_COMMUNITY,
            'stable' => false,
            'current' => false,
        ],
        [
            'id' => '1.0.0',
            'name' => 'Version 1.0.0 EE',
            'package' => SystemPackage::EDITION_ENTERPRISE,
            'stable' => true,
            'current' => true,
        ],
        [
            'id' => '1.0.0',
            'name' => 'Version 1.0.0 CE',
            'package' => SystemPackage::EDITION_COMMUNITY,
            'stable' => true,
            'current' => true,
        ],
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    public function setUp()
    {
        $this->composerAppFactory = $this->getMock(
            \Magento\Framework\Composer\MagentoComposerApplicationFactory::class,
            [],
            [],
            '',
            false
        );

        $this->infoCommand = $this->getMock(
            \Magento\Composer\InfoCommand::class,
            [],
            [],
            '',
            false
        );

        $this->magentoComposerApp =
            $this->getMock(\Magento\Composer\MagentoComposerApplication::class, [], [], '', false);
        $this->locker = $this->getMock(\Composer\Package\Locker::class, [], [], '', false);
        $this->repository = $this->getMock(\Composer\Repository\ArrayRepository::class, [], [], '', false);
        $this->composer = $this->getMock(\Composer\Composer::class, [], [], '', false);
        $this->composerInformation = $this->getMock(
            \Magento\Framework\Composer\ComposerInformation::class,
            [],
            [],
            '',
            false
        );
    }

    public function testGetPackageVersions()
    {
        $communityPackage = $this->getMock(\Composer\Package\Package::class, [], [], '', false);
        $communityPackage->expects($this->once())->method('getName')->willReturn(SystemPackage::EDITION_COMMUNITY);
        $enterprisePackage = $this->getMock(\Composer\Package\Package::class, [], [], '', false);
        $enterprisePackage->expects($this->once())->method('getName')->willReturn(SystemPackage::EDITION_ENTERPRISE);
        $this->composerInformation->expects($this->any())->method('isSystemPackage')->willReturn(true);
        $this->composerInformation->expects($this->once())->method('isPackageInComposerJson')->willReturn(true);
        $this->repository
            ->expects($this->once())
            ->method('getPackages')
            ->willReturn([$communityPackage, $enterprisePackage]);

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

        $this->infoCommand->expects($this->any())
            ->method('run')
            ->willReturnMap([
                [
                    SystemPackage::EDITION_COMMUNITY,
                    false,
                    [
                        'name' => SystemPackage::EDITION_COMMUNITY,
                        'description' => 'eCommerce Platform for Growth (Enterprise Edition)',
                        'keywords' => '',
                        'versions' => '1.2.0, 1.1.0, 1.1.0-RC1, * 1.0.0',
                        'type' => 'metapackage',
                        'license' => 'OSL-3.0, AFL-3.0',
                        'source' => '[]',
                        'names' => SystemPackage::EDITION_COMMUNITY,
                        'current_version' => '1.0.0',
                        InfoCommand::AVAILABLE_VERSIONS => [1 => '1.2.0', 2 => '1.1.0', 3 => '1.1.0-RC1', 4 => '1.0.0'],
                        'new_versions' => ['1.2.0', '1.1.0', '1.1.0-RC1'],
                    ],
                ],
                [
                    SystemPackage::EDITION_ENTERPRISE,
                    false,
                    [
                        'name' => SystemPackage::EDITION_ENTERPRISE,
                        'description' => 'eCommerce Platform for Growth (Enterprise Edition)',
                        'keywords' => '',
                        'versions' => '1.2.0, 1.1.0, 1.1.0-RC1, * 1.0.0',
                        'type' => 'metapackage',
                        'license' => 'OSL-3.0, AFL-3.0',
                        'source' => '[]',
                        'names' => SystemPackage::EDITION_ENTERPRISE,
                        'current_version' => '1.0.0',
                        InfoCommand::AVAILABLE_VERSIONS => [1 => '1.2.0', 2 => '1.1.0', 3 => '1.1.0-RC1', 4 => '1.0.0'],
                        'new_versions' => ['1.2.0', '1.1.0', '1.1.0-RC1'],
                    ],

                ],
                [

                    SystemPackage::EDITION_B2B,
                    false,
                    [
                        'name' => SystemPackage::EDITION_B2B,
                        'description' => 'eCommerce Platform for Growth (B2B Edition)',
                        'keywords' => '',
                        'versions' => '1.2.0, 1.1.0, 1.1.0-RC1, * 1.0.0',
                        'type' => 'metapackage',
                        'license' => 'OSL-3.0, AFL-3.0',
                        'source' => '[]',
                        'names' => SystemPackage::EDITION_B2B,
                        InfoCommand::AVAILABLE_VERSIONS => [],
                        'new_versions' => ['1.2.0', '1.1.0', '1.1.0-RC1'],
                    ],
                ],
            ]);
        $this->assertEquals($this->expectedPackages, $this->systemPackage->getPackageVersions());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage no components are available because you cloned the Magento 2 GitHub repository
     */
    public function testGetPackageVersionGitCloned()
    {
        $package = $this->getMock(\Composer\Package\Package::class, [], [], '', false);
        $this->repository
            ->expects($this->once())
            ->method('getPackages')
            ->willReturn([$package]);

        $this->locker->expects($this->once())->method('getLockedRepository')->willReturn($this->repository);
        $this->composerInformation->expects($this->any())->method('isSystemPackage')->willReturn(false);
        $this->composer->expects($this->once())->method('getLocker')->willReturn($this->locker);
        $this->magentoComposerApp->expects($this->once())->method('createComposer')->willReturn($this->composer);

        $this->composerAppFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->magentoComposerApp);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);
        $this->systemPackage->getPackageVersions();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage We cannot retrieve information on magento/product-community-edition.
     */
    public function testGetPackageVersionsFailed()
    {
        $communityPackage = $this->getMock(\Composer\Package\Package::class, [], [], '', false);
        $enterprisePackage = $this->getMock(\Composer\Package\Package::class, [], [], '', false);

        $communityPackage->expects($this->once())->method('getName')->willReturn(SystemPackage::EDITION_COMMUNITY);
        $enterprisePackage->expects($this->once())->method('getName')->willReturn(SystemPackage::EDITION_ENTERPRISE);
        $this->composerInformation->expects($this->any())->method('isSystemPackage')->willReturn(true);
        $this->composerInformation->expects($this->once())->method('isPackageInComposerJson')->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('getPackages')
            ->willReturn([$communityPackage, $enterprisePackage]);

        $this->locker->expects($this->once())->method('getLockedRepository')->willReturn($this->repository);

        $this->composer->expects($this->once())->method('getLocker')->willReturn($this->locker);
        $this->magentoComposerApp->expects($this->once())->method('createComposer')->willReturn($this->composer);

        $this->composerAppFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->magentoComposerApp);

        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);

        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);

        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with(SystemPackage::EDITION_COMMUNITY)
            ->willReturn(false);

        $this->systemPackage->getPackageVersions();
    }

    /**
     * @param string $ceCurrentVersion
     * @param array $expectedResult
     *
     * @dataProvider getAllowedEnterpriseVersionsDataProvider
     */
    public function testGetAllowedEnterpriseVersions($ceCurrentVersion, $expectedResult)
    {
        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);
        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);
        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with(SystemPackage::EDITION_ENTERPRISE)
            ->willReturn([InfoCommand::AVAILABLE_VERSIONS => ['1.0.0', '1.0.1', '1.0.2']]);
        $require = $this->getMock(\Composer\Package\Link::class, [], [], '', false);
        $constraintMock = $this->getMock(\Composer\Semver\Constraint\Constraint::class, [], [], '', false);
        $constraintMock->expects($this->any())->method('getPrettyString')
            ->willReturn('1.0.1');
        $require->expects($this->any())
            ->method('getConstraint')
            ->willReturn($constraintMock);

        $this->composerInformation->expects($this->any())
            ->method('getPackageRequirements')
            ->willReturn([SystemPackage::EDITION_COMMUNITY => $require]);
        $this->assertEquals(
            $expectedResult,
            $this->systemPackage->getAllowedEnterpriseVersions($ceCurrentVersion)
        );
    }

    /**
     * @param string $eeCurrentVersion
     * @param array $expectedResult
     *
     * @dataProvider getAllowedB2bVersionsDataProvider
     */
    public function testGetAllowedB2bVersions($eeCurrentVersion, $expectedResult)
    {
        $this->composerAppFactory->expects($this->once())
            ->method('createInfoCommand')
            ->willReturn($this->infoCommand);
        $this->systemPackage = new SystemPackage($this->composerAppFactory, $this->composerInformation);
        $this->infoCommand->expects($this->once())
            ->method('run')
            ->with(SystemPackage::EDITION_B2B)
            ->willReturn([InfoCommand::AVAILABLE_VERSIONS => ['1.0.0', '1.0.1', '1.0.2']]);
        $require = $this->getMock(\Composer\Package\Link::class, [], [], '', false);
        $constraintMock = $this->getMock(\Composer\Semver\Constraint\Constraint::class, [], [], '', false);
        $constraintMock->expects($this->any())->method('getPrettyString')
            ->willReturn('1.0.1');
        $require->expects($this->any())
            ->method('getConstraint')
            ->willReturn($constraintMock);

        $this->composerInformation->expects($this->any())
            ->method('getPackageRequirements')
            ->willReturn([SystemPackage::EDITION_ENTERPRISE => $require]);
        $this->assertEquals(
            $expectedResult,
            $this->systemPackage->getAllowedB2BVersions($eeCurrentVersion)
        );
    }

    /**
     * @return array
     */
    public function getAllowedEnterpriseVersionsDataProvider()
    {
        return [
            ['2.0.0', []],
            [
                '1.0.0',
                [
                    [
                        'package' => SystemPackage::EDITION_ENTERPRISE,
                        'versions' => [
                            [
                                'id' => '1.0.2',
                                'name' => 'Version 1.0.2 EE (latest)',
                                'current' => false,
                            ],
                            [
                                'id' => '1.0.1',
                                'name' => 'Version 1.0.1 EE',
                                'current' => false,
                            ],
                            [

                                'id' => '1.0.0',
                                'name' => 'Version 1.0.0 EE',
                                'current' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAllowedB2bVersionsDataProvider()
    {
        return [
            ['2.0.0', []],
            [
                '1.0.0',
                [
                    [
                        'package' => SystemPackage::EDITION_B2B,
                        'versions' => [
                            [
                                'id' => '1.0.2',
                                'name' => 'Version 1.0.2 B2B (latest)',
                                'current' => false,
                            ],
                            [
                                'id' => '1.0.1',
                                'name' => 'Version 1.0.1 B2B',
                                'current' => false,
                            ],
                            [

                                'id' => '1.0.0',
                                'name' => 'Version 1.0.0 B2B',
                                'current' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

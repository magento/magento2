<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Semver\Constraint\Constraint;
use Magento\Composer\InfoCommand;
use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Setup\Model\SystemPackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SystemPackageTest extends TestCase
{
    /**
     * @var MockObject|InfoCommand
     */
    private $infoCommand;

    /**
     * @var MockObject|SystemPackage ;
     */
    private $systemPackage;

    /**
     * @var MockObject|ArrayRepository
     */
    private $repository;

    /**
     * @var MockObject|Locker
     */
    private $locker;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|/Magento\Composer\MagentoComposerApplication
     */
    private $magentoComposerApp;

    /**
     * @var MockObject|MagentoComposerApplicationFactory
     */
    private $composerAppFactory;

    /**
     * @var MockObject|Composer
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
     * @var MockObject|ComposerInformation
     */
    private $composerInformation;

    protected function setUp(): void
    {
        $this->composerAppFactory = $this->createMock(
            MagentoComposerApplicationFactory::class
        );

        $this->infoCommand = $this->createMock(
            InfoCommand::class
        );

        $this->magentoComposerApp =
            $this->createMock(MagentoComposerApplication::class);
        $this->locker = $this->createMock(Locker::class);
        $this->repository = $this->createMock(ArrayRepository::class);
        $this->composer = $this->createMock(Composer::class);
        $this->composerInformation = $this->createMock(
            ComposerInformation::class
        );
    }

    public function testGetPackageVersions()
    {
        $communityPackage = $this->createMock(Package::class);
        $communityPackage->expects($this->once())->method('getName')->willReturn(SystemPackage::EDITION_COMMUNITY);
        $enterprisePackage = $this->createMock(Package::class);
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

                ]
            ]);
        $this->assertEquals($this->expectedPackages, $this->systemPackage->getPackageVersions());
    }

    public function testGetPackageVersionGitCloned()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('no components are available because you cloned the Magento 2 GitHub repository');
        $package = $this->createMock(Package::class);
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

    public function testGetPackageVersionsFailed()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('We cannot retrieve information on magento/product-community-edition.');
        $communityPackage = $this->createMock(Package::class);
        $enterprisePackage = $this->createMock(Package::class);

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
        $require = $this->createMock(Link::class);
        $constraintMock = $this->createMock(Constraint::class);
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
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    /**
     * @var array
     */
    private $expectedPackages = [
        [
            'id' => '1.2.0',
            'name' => 'Version 1.2.0 EE (latest)',
            'package' => 'magento/product-enterprise-edition',
            'stable' => true
        ],
        [
            'id' => '1.2.0',
            'name' => 'Version 1.2.0 CE (latest)',
            'package' => 'magento/product-community-edition',
            'stable' => true
        ],
        [
            'id' => '1.1.0',
            'name' => 'Version 1.1.0 EE',
            'package' => 'magento/product-enterprise-edition',
            'stable' => true
        ],
        [
            'id' => '1.1.0',
            'name' => 'Version 1.1.0 CE',
            'package' => 'magento/product-community-edition',
            'stable' => true
        ],
        [
            'id' => '1.1.0-RC1',
            'name' => 'Version 1.1.0-RC1 EE (unstable version)',
            'package' => 'magento/product-enterprise-edition',
            'stable' => false
        ],
        [
            'id' => '1.1.0-RC1',
            'name' => 'Version 1.1.0-RC1 CE (unstable version)',
            'package' => 'magento/product-community-edition',
            'stable' => false
        ],
        [
            'id' => '1.0.0',
            'name' => 'Version 1.0.0 EE (current)',
            'package' => 'magento/product-enterprise-edition',
            'stable' => true
        ],
        [
            'id' => '1.0.0',
            'name' => 'Version 1.0.0 CE (current)',
            'package' => 'magento/product-community-edition',
            'stable' => true
        ],
    ];

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
        $communityPackage = $this->getMock('\Composer\Package\Package', [], [], '', false);
        $communityPackage->expects($this->once())->method('getName')->willReturn('magento/product-community-edition');
        $enterprisePackage = $this->getMock('\Composer\Package\Package', [], [], '', false);
        $enterprisePackage->expects($this->once())->method('getName')->willReturn('magento/product-enterprise-edition');
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

        $this->infoCommand->expects($this->at(0))
            ->method('run')
            ->with('magento/product-community-edition')
            ->willReturn(
                [
                    'name' => 'magento/product-community-edition',
                    'description' => 'eCommerce Platform for Growth (Enterprise Edition)',
                    'keywords' => '',
                    'versions' => '1.2.0, 1.1.0, 1.1.0-RC1, * 1.0.0',
                    'type' => 'metapackage',
                    'license' => 'OSL-3.0, AFL-3.0',
                    'source' => '[]',
                    'names' => 'magento/product-community-edition',
                    'current_version' => '1.0.0',
                    'available_versions' => [1 => '1.2.0', 2 => '1.1.0', 3 => '1.1.0-RC1', 4 => '1.0.0'],
                    'new_versions' => ['1.2.0', '1.1.0', '1.1.0-RC1']
                ]
            );

        $this->infoCommand->expects($this->at(1))
            ->method('run')
            ->with('magento/product-enterprise-edition')
            ->willReturn(
                [
                    'name' => 'magento/product-enterprise-edition',
                    'description' => 'eCommerce Platform for Growth (Enterprise Edition)',
                    'keywords' => '',
                    'versions' => '1.2.0, 1.1.0, 1.1.0-RC1, * 1.0.0',
                    'type' => 'metapackage',
                    'license' => 'OSL-3.0, AFL-3.0',
                    'source' => '[]',
                    'names' => 'magento/product-enterprise-edition',
                    'current_version' => '1.0.0',
                    'available_versions' => [1 => '1.2.0', 2 => '1.1.0', 3 => '1.1.0-RC1', 4 => '1.0.0'],
                    'new_versions' => ['1.2.0', '1.1.0', '1.1.0-RC1']
                ]
            );
        $this->assertEquals($this->expectedPackages, $this->systemPackage->getPackageVersions());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage no components are available because you cloned the Magento 2 GitHub repository
     */
    public function testGetPackageVersionGitCloned()
    {
        $package = $this->getMock('\Composer\Package\Package', [], [], '', false);
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
        $communityPackage = $this->getMock('\Composer\Package\Package', [], [], '', false);
        $enterprisePackage = $this->getMock('\Composer\Package\Package', [], [], '', false);

        $communityPackage->expects($this->once())->method('getName')->willReturn('magento/product-community-edition');
        $enterprisePackage->expects($this->once())->method('getName')->willReturn('magento/product-enterprise-edition');
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
            ->with('magento/product-community-edition')
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
            ->with('magento/product-enterprise-edition')
            ->willReturn(['available_versions' => ['1.0.0', '1.0.1', '1.0.2']]);
        $require = $this->getMock('\Composer\Package\Link', [], [], '', false);
        $constraintMock = $this->getMock('\Composer\Semver\Constraint\Constraint', [], [], '', false);
        $constraintMock->expects($this->any())->method('getPrettyString')
            ->willReturn('1.0.1');
        $require->expects($this->any())
            ->method('getConstraint')
            ->willReturn($constraintMock);

        $this->composerInformation->expects($this->any())
            ->method('getPackageRequirements')
            ->willReturn(['magento/product-community-edition' => $require]);
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
            ['1.0.0', [
                [
                    'package' => 'magento/product-enterprise-edition',
                    'versions' => [
                        [
                            'id' => '1.0.2',
                            'name' => 'Version 1.0.2 EE (latest)'
                        ],
                        [
                            'id' => '1.0.1',
                            'name' => 'Version 1.0.1 EE'
                        ],
                        [

                            'id' => '1.0.0',
                            'name' => 'Version 1.0.0 EE'
                        ]
                    ]
                ]
            ]
            ]
        ];
    }
}

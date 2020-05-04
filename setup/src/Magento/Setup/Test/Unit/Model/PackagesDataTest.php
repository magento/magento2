<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Composer\Package\RootPackage;
use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Setup\Model\DateTime\TimeZoneProvider;
use Magento\Setup\Model\Grid\TypeMapper;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Setup\Model\PackagesData
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackagesDataTest extends TestCase
{
    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @var ComposerInformation|MockObject
     */
    private $composerInformation;

    /**
     * @var TimeZoneProvider|MockObject
     */
    private $timeZoneProvider;

    /**
     * @var PackagesAuth|MockObject
     */
    private $packagesAuth;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    /**
     * @var TypeMapper|MockObject
     */
    private $typeMapper;

    protected function setUp(): void
    {
        $this->composerInformation = $this->getComposerInformation();
        $this->timeZoneProvider = $this->getMockBuilder(TimeZoneProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeZone = $this->createMock(Timezone::class);
        $this->timeZoneProvider->expects($this->any())->method('get')->willReturn($timeZone);
        $this->packagesAuth = $this->createMock(PackagesAuth::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->objectManagerProvider = $this->getMockBuilder(ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $appFactory = $this->getMockBuilder(MagentoComposerApplicationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $application = $this->createMock(MagentoComposerApplication::class);
        $application->expects($this->any())
            ->method('runComposerCommand')
            ->willReturnMap([
                [
                    [
                        PackagesData::PARAM_COMMAND => PackagesData::COMPOSER_SHOW,
                        PackagesData::PARAM_PACKAGE => 'magento/package-1',
                        PackagesData::PARAM_AVAILABLE => true,
                    ],
                    null,
                    'versions: 2.0.1'
                ],
                [
                    [
                        PackagesData::PARAM_COMMAND => PackagesData::COMPOSER_SHOW,
                        PackagesData::PARAM_PACKAGE => 'magento/package-2',
                        PackagesData::PARAM_AVAILABLE => true,
                    ],
                    null,
                    'versions: 2.0.1'
                ],
                [
                    [
                        PackagesData::PARAM_COMMAND => PackagesData::COMPOSER_SHOW,
                        PackagesData::PARAM_PACKAGE => 'partner/package-3',
                        PackagesData::PARAM_AVAILABLE => true,
                    ],
                    null,
                    'versions: 3.0.1'
                ],
            ]);
        $appFactory->expects($this->any())->method('create')->willReturn($application);
        $objectManager->expects($this->any())
            ->method('get')
            ->with(MagentoComposerApplicationFactory::class)
            ->willReturn($appFactory);
        $this->objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);

        $directoryWrite = $this->getMockForAbstractClass(WriteInterface::class);
        $directoryRead = $this->getMockForAbstractClass(ReadInterface::class);
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($directoryRead);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($directoryWrite);
        $directoryWrite->expects($this->any())->method('isExist')->willReturn(true);
        $directoryWrite->expects($this->any())->method('isReadable')->willReturn(true);
        $directoryWrite->expects($this->any())->method('delete')->willReturn(true);
        $directoryRead->expects($this->any())->method('isExist')->willReturn(true);
        $directoryRead->expects($this->any())->method('isReadable')->willReturn(true);
        $directoryRead->expects($this->any())->method('stat')->willReturn(['mtime' => '1462460216076']);
        $directoryRead->expects($this->any())
            ->method('readFile')
            ->willReturn(
                '{"packages":{"magento\/package-1":{'
                . '"1.0.0":{"name":"magento\/package-1","version":"1.0.0","vendor":"test","type":"metapackage",'
                . '"require":{"magento\/package-3":"1.0.0"}},'
                . '"1.0.1":{"name":"magento\/package-1","version":"1.0.1","vendor":"test","type":"magento2-module"},'
                . '"1.0.2":{"name":"magento\/package-1","version":"1.0.2","vendor":"test","type":"magento2-module"}'
                . '}, "magento\/package-2":{'
                . '"1.0.0":{"name":"magento\/package-2","version":"1.0.0","vendor":"test","type":"magento2-module"},'
                . '"1.0.1":{"name":"magento\/package-2","version":"1.0.1","vendor":"test","type":"magento2-module"}'
                . '}, "magento\/package-3":{'
                . '"1.0.0":{"name":"magento\/package-3","version":"1.0.0","vendor":"test","type":"magento2-module"},'
                . '"1.0.1":{"name":"magento\/package-3","version":"1.0.1","vendor":"test","type":"magento2-module"},'
                . '"1.0.2":{"name":"magento\/package-3","version":"1.0.2","vendor":"test","type":"magento2-module",'
                . '"extra":{"x-magento-ext-title":"Package 3 title", "x-magento-ext-type":"Extension"}}'
                . '}}}'
            );

        $this->typeMapper = $this->getMockBuilder(TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMapper->expects(static::any())
            ->method('map')
            ->willReturnMap([
                [ComposerInformation::MODULE_PACKAGE_TYPE, TypeMapper::MODULE_PACKAGE_TYPE],
            ]);

        $this->createPackagesData();
    }

    private function createPackagesData()
    {
        $this->packagesData = new PackagesData(
            $this->composerInformation,
            $this->timeZoneProvider,
            $this->packagesAuth,
            $this->filesystem,
            $this->objectManagerProvider,
            $this->typeMapper
        );
    }

    /**
     * @param array $requiredPackages
     * @param array $installedPackages
     * @param array $repo
     * @return ComposerInformation|MockObject
     */
    private function getComposerInformation($requiredPackages = [], $installedPackages = [], $repo = [])
    {
        $composerInformation = $this->createMock(ComposerInformation::class);
        $composerInformation->expects($this->any())->method('getInstalledMagentoPackages')->willReturn(
            $installedPackages ?:
            [
                'magento/package-1' => [
                    'name' => 'magento/package-1',
                    'type' => 'magento2-module',
                    'version'=> '1.0.0'
                ],
                'magento/package-2' => [
                    'name' => 'magento/package-2',
                    'type' => 'magento2-module',
                    'version'=> '1.0.1'
                ],
                'partner/package-3' => [
                    'name' => 'partner/package-3',
                    'type' => 'magento2-module',
                    'version'=> '3.0.0'
                ],
            ]
        );

        $composerInformation->expects($this->any())->method('getRootRepositories')
            ->willReturn($repo ?: ['repo1', 'repo2']);
        $composerInformation->expects($this->any())->method('getPackagesTypes')
            ->willReturn(['magento2-module']);
        $rootPackage = $this->getMockBuilder(RootPackage::class)
            ->setConstructorArgs(['magento/project', '2.1.0', '2'])
            ->getMock();
        $rootPackage->expects($this->any())
            ->method('getRequires')
            ->willReturn(
                $requiredPackages ?:
                [
                    'magento/package-1' => '1.0.0',
                    'magento/package-2' => '1.0.1',
                    'partner/package-3' => '3.0.0',
                ]
            );
        $composerInformation->expects($this->any())
            ->method('getRootPackage')
            ->willReturn($rootPackage);

        return $composerInformation;
    }

    public function testSyncPackagesData()
    {
        $latestData = $this->packagesData->syncPackagesData();
        $this->assertArrayHasKey('lastSyncDate', $latestData);
        $this->assertArrayHasKey('date', $latestData['lastSyncDate']);
        $this->assertArrayHasKey('time', $latestData['lastSyncDate']);
        $this->assertArrayHasKey('packages', $latestData);
        $this->assertCount(3, $latestData['packages']);
        $this->assertSame(3, $latestData['countOfUpdate']);
        $this->assertArrayHasKey('installPackages', $latestData);
        $this->assertCount(1, $latestData['installPackages']);
        $this->assertSame(1, $latestData['countOfInstall']);
    }

    public function testGetPackagesForUpdateWithException()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Couldn\'t get available versions for package partner/package-4');
        $requiredPackages = [
            'partner/package-4' => '4.0.4',
        ];
        $installedPackages = [
            'partner/package-4' => [
                'name' => 'partner/package-4',
                'type' => 'magento2-module',
                'version'=> '4.0.4'
            ],
        ];
        $this->composerInformation = $this->getComposerInformation($requiredPackages, $installedPackages);
        $this->createPackagesData();
        $this->packagesData->getPackagesForUpdate();
    }

    public function testPackagesForUpdateFromJson()
    {
        $this->composerInformation = $this->getComposerInformation([], [], ['https://repo1']);
        $this->packagesAuth->expects($this->atLeastOnce())
            ->method('getCredentialBaseUrl')
            ->willReturn('repo1');
        $this->createPackagesData();
        $packages = $this->packagesData->getPackagesForUpdate();
        $this->assertCount(2, $packages);
        $this->assertArrayHasKey('magento/package-1', $packages);
        $this->assertArrayHasKey('partner/package-3', $packages);
        $firstPackage = array_values($packages)[0];
        $this->assertArrayHasKey('latestVersion', $firstPackage);
        $this->assertArrayHasKey('versions', $firstPackage);
    }

    public function testGetPackagesForUpdate()
    {
        $packages = $this->packagesData->getPackagesForUpdate();
        $this->assertCount(3, $packages);
        $this->assertArrayHasKey('magento/package-1', $packages);
        $this->assertArrayHasKey('magento/package-2', $packages);
        $this->assertArrayHasKey('partner/package-3', $packages);
        $firstPackage = array_values($packages)[0];
        $this->assertArrayHasKey('latestVersion', $firstPackage);
        $this->assertArrayHasKey('versions', $firstPackage);
    }

    public function testGetInstalledPackages()
    {
        $installedPackages = $this->packagesData->getInstalledPackages();
        $this->assertCount(3, $installedPackages);
        $this->assertArrayHasKey('magento/package-1', $installedPackages);
        $this->assertArrayHasKey('magento/package-2', $installedPackages);
        $this->assertArrayHasKey('partner/package-3', $installedPackages);
    }

    public function testGetMetaPackagesMap()
    {
        static::assertEquals(
            ['magento/package-3' => 'magento/package-1'],
            $this->packagesData->getMetaPackagesMap()
        );
    }

    public function testAddPackageExtraInfo()
    {
        static::assertEquals(
            [
                'package_title' => 'Package 3 title',
                'package_type' => 'Extension',
                'name' => 'magento/package-3',
                'version' => '1.0.2',
                'package_link' => ''
            ],
            $this->packagesData->addPackageExtraInfo(['name' => 'magento/package-3', 'version' => '1.0.2'])
        );
    }
}

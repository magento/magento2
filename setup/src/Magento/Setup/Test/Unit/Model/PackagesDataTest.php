<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Composer\Package\RootPackage;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\PackagesData;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests Magento\Setup\Model\PackagesData
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackagesDataTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Setup\Model\DateTime\TimeZoneProvider|MockObject
     */
    private $timeZoneProvider;

    /**
     * @var \Magento\Setup\Model\PackagesAuth|MockObject
     */
    private $packagesAuth;

    /**
     * @var \Magento\Framework\Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Setup\Model\Grid\TypeMapper|MockObject
     */
    private $typeMapper;

    public function setUp()
    {
        $this->composerInformation = $this->getComposerInformation();
        $this->timeZoneProvider = $this->getMockBuilder(\Magento\Setup\Model\DateTime\TimeZoneProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $timeZone = $this->getMock(\Magento\Framework\Stdlib\DateTime\Timezone::class, [], [], '', false);
        $this->timeZoneProvider->expects($this->any())->method('get')->willReturn($timeZone);
        $this->packagesAuth = $this->getMock(\Magento\Setup\Model\PackagesAuth::class, [], [], '', false);
        $this->filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->objectManagerProvider = $this->getMockBuilder(\Magento\Setup\Model\ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $appFactory = $this->getMockBuilder(\Magento\Framework\Composer\MagentoComposerApplicationFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $application = $this->getMock('Magento\Composer\MagentoComposerApplication', [], [], '', false);
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
            ->with(\Magento\Framework\Composer\MagentoComposerApplicationFactory::class)
            ->willReturn($appFactory);
        $this->objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);

        $directoryWrite = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $directoryRead = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($directoryRead));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($directoryWrite));
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

        $this->typeMapper = $this->getMockBuilder(\Magento\Setup\Model\Grid\TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeMapper->expects(static::any())
            ->method('map')
            ->willReturnMap([
                [ComposerInformation::MODULE_PACKAGE_TYPE, \Magento\Setup\Model\Grid\TypeMapper::MODULE_PACKAGE_TYPE],
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
        $composerInformation = $this->getMock(ComposerInformation::class, [], [], '', false);
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
        $rootPackage = $this->getMock(RootPackage::class, [], ['magento/project', '2.1.0', '2']);
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
        $this->assertSame(3, count($latestData['packages']));
        $this->assertSame(3, $latestData['countOfUpdate']);
        $this->assertArrayHasKey('installPackages', $latestData);
        $this->assertSame(1, count($latestData['installPackages']));
        $this->assertSame(1, $latestData['countOfInstall']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Couldn't get available versions for package partner/package-4
     */
    public function testGetPackagesForUpdateWithException()
    {
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
        $this->assertEquals(2, count($packages));
        $this->assertArrayHasKey('magento/package-1', $packages);
        $this->assertArrayHasKey('partner/package-3', $packages);
        $firstPackage = array_values($packages)[0];
        $this->assertArrayHasKey('latestVersion', $firstPackage);
        $this->assertArrayHasKey('versions', $firstPackage);
    }

    public function testGetPackagesForUpdate()
    {
        $packages = $this->packagesData->getPackagesForUpdate();
        $this->assertEquals(3, count($packages));
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
        $this->assertEquals(3, count($installedPackages));
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

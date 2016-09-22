<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

    public function setUp()
    {
        $composerInformation = $this->getComposerInformation();
        $timeZoneProvider = $this->getMock(\Magento\Setup\Model\DateTime\TimeZoneProvider::class, [], [], '', false);
        $timeZone = $this->getMock(\Magento\Framework\Stdlib\DateTime\Timezone::class, [], [], '', false);
        $timeZoneProvider->expects($this->any())->method('get')->willReturn($timeZone);
        $packagesAuth = $this->getMock(\Magento\Setup\Model\PackagesAuth::class, [], [], '', false);
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $applicationFactory = $this->getMock(
            \Magento\Framework\Composer\MagentoComposerApplicationFactory::class,
            [],
            [],
            '',
            false
        );
        $application = $this->getMock(\Magento\Composer\MagentoComposerApplication::class, [], [], '', false);
        $application->expects($this->any())
            ->method('runComposerCommand')
            ->willReturn('versions: 2.0.1');
        $applicationFactory->expects($this->any())->method('create')->willReturn($application);
        $objectManager->expects($this->any())
            ->method('get')
            ->with(\Magento\Framework\Composer\MagentoComposerApplicationFactory::class)
            ->willReturn($applicationFactory);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);

        $directoryWrite = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $directoryRead = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($directoryRead));
        $filesystem->expects($this->any())
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

        $typeMapper = $this->getMockBuilder(\Magento\Setup\Model\Grid\TypeMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeMapper->expects(static::any())
            ->method('map')
            ->willReturnMap([
                [ComposerInformation::MODULE_PACKAGE_TYPE, \Magento\Setup\Model\Grid\TypeMapper::MODULE_PACKAGE_TYPE],
            ]);

        $this->packagesData = new PackagesData(
            $composerInformation,
            $timeZoneProvider,
            $packagesAuth,
            $filesystem,
            $objectManagerProvider,
            $typeMapper
        );
    }

    /**
     * @return ComposerInformation|MockObject
     */
    private function getComposerInformation()
    {
        $composerInformation = $this->getMock(ComposerInformation::class, [], [], '', false);
        $composerInformation->expects($this->any())->method('getInstalledMagentoPackages')->willReturn(
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
                ]
            ]
        );

        $composerInformation->expects($this->any())->method('getRootRepositories')
            ->willReturn(['repo1', 'repo2']);
        $composerInformation->expects($this->any())->method('getPackagesTypes')
            ->willReturn(['magento2-module']);
        $rootPackage = $this->getMock(RootPackage::class, [], ['magento/project', '2.1.0', '2']);
        $rootPackage->expects($this->any())
            ->method('getRequires')
            ->willReturn([
                'magento/package-1' => '1.0.0',
                'magento/package-2' => '1.0.1'
            ]);
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
        $this->assertSame(2, count($latestData['packages']));
        $this->assertSame(2, $latestData['countOfUpdate']);
        $this->assertArrayHasKey('installPackages', $latestData);
        $this->assertSame(1, count($latestData['installPackages']));
        $this->assertSame(1, $latestData['countOfInstall']);
    }

    public function testGetPackagesForUpdate()
    {
        $packages = $this->packagesData->getPackagesForUpdate();
        $this->assertEquals(2, count($packages));
        $this->assertArrayHasKey('magento/package-1', $packages);
        $this->assertArrayHasKey('magento/package-2', $packages);
        $firstPackage = array_values($packages)[0];
        $this->assertArrayHasKey('latestVersion', $firstPackage);
        $this->assertArrayHasKey('versions', $firstPackage);
    }

    public function testGetInstalledPackages()
    {
        $installedPackages = $this->packagesData->getInstalledPackages();
        $this->assertEquals(2, count($installedPackages));
        $this->assertArrayHasKey('magento/package-1', $installedPackages);
        $this->assertArrayHasKey('magento/package-2', $installedPackages);
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

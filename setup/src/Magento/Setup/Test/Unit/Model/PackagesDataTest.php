<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PackagesData;

/**
 * Tests Magento\Setup\Model\PackagesData
 */
class PackagesDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PackagesData
     */
    private $packagesData;

    public function setUp()
    {
        $composerInformation =
            $this->getMock(\Magento\Framework\Composer\ComposerInformation::class, [], [], '', false);
        $composerInformation->expects($this->any())->method('getInstalledMagentoPackages')->willReturn(
            [
                ['name' => 'magento/package-1', 'type' => 'magento2-module', 'version'=> '1.0.0'],
                ['name' => 'magento/package-2', 'type' => 'magento2-module', 'version'=> '1.0.1']
            ]
        );

        $composerInformation->expects($this->any())->method('getRootRepositories')->willReturn(['repo1', 'repo2']);
        $composerInformation->expects($this->any())->method('getPackagesTypes')->willReturn(['magento2-module']);
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
                . '"1.0.0":{"name":"magento\/package-1","version":"1.0.0","vendor":"test","type":"magento2-module"},'
                . '"1.0.1":{"name":"magento\/package-1","version":"1.0.1","vendor":"test","type":"magento2-module"},'
                . '"1.0.2":{"name":"magento\/package-1","version":"1.0.2","vendor":"test","type":"magento2-module"}'
                . '}, "magento\/package-2":{'
                . '"1.0.0":{"name":"magento\/package-2","version":"1.0.0","vendor":"test","type":"magento2-module"},'
                . '"1.0.1":{"name":"magento\/package-2","version":"1.0.1","vendor":"test","type":"magento2-module"}'
                . '}, "magento\/package-3":{'
                . '"1.0.0":{"name":"magento\/package-3","version":"1.0.0","vendor":"test","type":"magento2-module"},'
                . '"1.0.1":{"name":"magento\/package-3","version":"1.0.1","vendor":"test","type":"magento2-module"},'
                . '"1.0.2":{"name":"magento\/package-3","version":"1.0.2","vendor":"test","type":"magento2-module"}'
                . '}}}'
            );

        $this->packagesData = new PackagesData(
            $composerInformation,
            $timeZoneProvider,
            $packagesAuth,
            $filesystem,
            $objectManagerProvider
        );
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
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;

/**
 * Tests Magento\Framework\ComposerInformation
 */
class UpdatePackagesCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var ComposerJsonFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerJsonFinder;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Composer\ComposerInformation $composerInformation
     */
    private $composerInformation;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Setup DirectoryList, Filesystem, and ComposerJsonFinder to use a specified directory for reading composer files
     *
     * @param $composerDir string Directory under _files that contains composer files
     */
    private function setupDirectory($composerDir)
    {
        $absoluteComposerDir = realpath(__DIR__ . '/_files/' . $composerDir . '/composer.json');
        $this->composerJsonFinder = $this->getMockBuilder('Magento\Framework\Composer\ComposerJsonFinder')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->composerJsonFinder->expects($this->any())->method('findComposerJson')->willReturn($absoluteComposerDir);

        $this->directoryList = $this->objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->filesystem = $this->objectManager->get('Magento\Framework\Filesystem');

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $this->composerInformation = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                )
            ]
        );
    }

    public function testGetPackagesForUpdate()
    {
        $packageName = 'magento/module-store';

        $this->setupDirectory('testSkeleton');

        /** @var UpdatePackagesCache $updatePackagesCache|\PHPUnit_Framework_MockObject_MockObject */
        $updatePackagesCache = $this->getMock('Magento\Setup\Model\UpdatePackagesCache', [], [], '', false);

        $packages = [
            'packages' => [
                $packageName => [
                    'latestVersion' => '1000.100.200'
                ]
            ]
        ];

        $updatePackagesCache->expects($this->once())->method('syncPackagesForUpdate')->willReturn(true);
        $updatePackagesCache->expects($this->once())->method('getPackagesForUpdate')->willReturn($packages);

        $requiredPackages = $this->composerInformation->getInstalledMagentoPackages();
        $this->assertArrayHasKey($packageName, $requiredPackages);

        $this->assertTrue($updatePackagesCache->syncPackagesForUpdate());

        $packagesForUpdate = $updatePackagesCache->getPackagesForUpdate();
        $this->assertArrayHasKey('packages', $packagesForUpdate);
        $this->assertArrayHasKey($packageName, $packagesForUpdate['packages']);
        $this->assertTrue(
            version_compare(
                $packagesForUpdate['packages'][$packageName]['latestVersion'],
                $requiredPackages[$packageName]['version'],
                '>'
            )
        );
    }
}

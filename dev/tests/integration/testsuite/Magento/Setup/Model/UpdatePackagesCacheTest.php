<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

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

        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->objectManager);

        /** @var \Magento\Setup\Model\UpdatePackagesCache $updatePackagesCache */
        $updatePackagesCache = $this->objectManager->create(
            'Magento\Setup\Model\UpdatePackagesCache',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
                'composerInformation' => $this->composerInformation,
                'objectManagerProvider' => $objectManagerProvider,
            ]
        );

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

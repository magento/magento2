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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var ComposerJsonFinder
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
        $directories = [
            DirectoryList::CONFIG => [DirectoryList::PATH => __DIR__ . '/_files/'],
            DirectoryList::ROOT => [DirectoryList::PATH => __DIR__ . '/_files/' . $composerDir],
            DirectoryList::COMPOSER_HOME => [DirectoryList::PATH => __DIR__ . '/_files/' . $composerDir],
        ];

        $this->directoryList = $this->objectManager->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            ['root' => __DIR__ . '/_files/' . $composerDir, 'config' => $directories]
        );

        $this->filesystem = $this->objectManager->create(
            'Magento\Framework\Filesystem',
            ['directoryList' => $this->directoryList]
        );

        $this->composerJsonFinder = new ComposerJsonFinder($this->directoryList);

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
        $packageName = 'magento/language-de_de';

        $this->setupDirectory('testSkeleton');

        /** @var \Magento\Setup\Model\UpdatePackagesCache $updatePackagesCache */
        $updatePackagesCache = $this->objectManager->create(
            'Magento\Setup\Model\UpdatePackagesCache',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
                'composerInfo' => $this->composerInformation
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

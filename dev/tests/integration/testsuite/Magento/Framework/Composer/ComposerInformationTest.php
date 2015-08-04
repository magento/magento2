<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests Magento\Framework\ComposerInformation
 */
class ComposerInformationTest extends \PHPUnit_Framework_TestCase
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
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRequiredPhpVersion($composerDir)
    {
        $this->setupDirectory($composerDir);

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $composerInfo = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );

        $this->assertEquals("~5.5.0|~5.6.0", $composerInfo->getRequiredPhpVersion());
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRequiredExtensions($composerDir)
    {
        $this->setupDirectory($composerDir);
        $expectedExtensions = ['ctype', 'gd', 'spl', 'dom', 'simplexml', 'mcrypt', 'hash', 'curl', 'iconv', 'intl'];

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $composerInfo = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );

        $actualRequiredExtensions = $composerInfo->getRequiredExtensions();
        foreach ($expectedExtensions as $expectedExtension) {
            $this->assertContains($expectedExtension, $actualRequiredExtensions);
        }
    }

    /**
     * @param $composerDir string Directory under _files that contains composer files
     *
     * @dataProvider getRequiredPhpVersionDataProvider
     */
    public function testGetRootRequiredPackagesAndTypes($composerDir)
    {
        $this->setupDirectory($composerDir);

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $composerInfo = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );

        $requiredPackagesAndTypes = $composerInfo->getRootRequiredPackageTypesByName();

        $this->assertArrayHasKey('composer/composer', $requiredPackagesAndTypes);
        $this->assertEquals('library', $requiredPackagesAndTypes['composer/composer']);
    }

    public function testGetPackagesForUpdate()
    {
        $packageName = 'magento/language-de_de';

        $this->setupDirectory('testSkeleton');

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $composerInfo = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );

        $requiredPackages = $composerInfo->getInstalledMagentoPackages();
        $this->assertArrayHasKey($packageName, $requiredPackages);

        $this->assertTrue($composerInfo->syncPackagesForUpdate());

        $packagesForUpdate = $composerInfo->getPackagesForUpdate();
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

    /**
     * Data provider that returns directories containing different types of composer files.
     *
     * @return array
     */
    public function getRequiredPhpVersionDataProvider()
    {
        return [
            'Skeleton Composer' => ['testSkeleton'],
            'Composer.json from git clone' => ['testFromClone'],
            'Composer.json from git create project' => ['testFromCreateProject'],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Composer file not found
     */
    public function testNoLock()
    {
        $this->setupDirectory('notARealDirectory');
        $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );
    }

    public function testIsPackageInComposerJson()
    {
        $this->setupDirectory('testSkeleton');

        /** @var \Magento\Framework\Composer\ComposerInformation $composerInfo */
        $composerInfo = $this->objectManager->create(
            'Magento\Framework\Composer\ComposerInformation',
            [
                'applicationFactory' => new MagentoComposerApplicationFactory(
                    $this->composerJsonFinder,
                    $this->directoryList
                ),
                'filesystem' => $this->filesystem,
            ]
        );

        $packageName = 'magento/sample-module-minimal';
        $this->assertTrue($composerInfo->isPackageInComposerJson($packageName));
        $packageName = 'magento/wrong-module-name';
        $this->assertFalse($composerInfo->isPackageInComposerJson($packageName));
    }
}

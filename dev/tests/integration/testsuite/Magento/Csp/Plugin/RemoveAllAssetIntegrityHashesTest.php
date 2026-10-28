<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for RemoveAllAssetIntegrityHashes plugin.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @group sri_renderer
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class RemoveAllAssetIntegrityHashesTest extends TestCase
{
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * @var RemoveAllAssetIntegrityHashes
     */
    private RemoveAllAssetIntegrityHashes $plugin;

    /**
     * @var DeployStaticContent
     */
    private DeployStaticContent $subject;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var string[]
     */
    private array $createdFiles = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->plugin = $objectManager->get(RemoveAllAssetIntegrityHashes::class);
        $this->subject = $objectManager->get(DeployStaticContent::class);
        $this->filesystem = $objectManager->get(Filesystem::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        foreach ($this->createdFiles as $file) {
            if ($dir->isFile($file)) {
                $dir->delete($file);
            }
        }
        $this->createdFiles = [];
        parent::tearDown();
    }

    /**
     * Full deploy (no constraints) must delete every sri-hashes.json file.
     */
    public function testFullDeployDeletesAllSriFiles(): void
    {
        $luma  = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $blank = 'frontend/Magento/blank/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($luma);
        $this->writeSriFile($blank);

        $this->plugin->beforeDeploy($this->subject, []);

        $this->assertSriFileNotExists($luma, 'Luma SRI file must be deleted on full deploy');
        $this->assertSriFileNotExists($blank, 'Blank SRI file must be deleted on full deploy');
    }

    /**
     * Partial deploy scoped by theme must only delete that theme's files.
     */
    public function testPartialDeployByThemePreservesBystanders(): void
    {
        $luma  = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $blank = 'frontend/Magento/blank/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($luma);
        $this->writeSriFile($blank);

        $this->plugin->beforeDeploy($this->subject, [
            Options::THEME => ['Magento/luma'],
        ]);

        $this->assertSriFileNotExists($luma, 'Luma SRI file must be deleted');
        $this->assertSriFileExists($blank, 'Blank SRI file must be preserved');
    }

    /**
     * Partial deploy scoped by area must only delete files in that area.
     */
    public function testPartialDeployByAreaPreservesBystanders(): void
    {
        $frontend  = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $adminhtml = 'adminhtml/Magento/backend/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($frontend);
        $this->writeSriFile($adminhtml);

        $this->plugin->beforeDeploy($this->subject, [
            Options::AREA => ['frontend'],
        ]);

        $this->assertSriFileNotExists($frontend, 'Frontend SRI file must be deleted');
        $this->assertSriFileExists($adminhtml, 'Adminhtml SRI file must be preserved');
    }

    /**
     * Partial deploy scoped by locale must only delete files for that locale.
     */
    public function testPartialDeployByLocalePreservesBystanders(): void
    {
        $enUS = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $frFR = 'frontend/Magento/luma/fr_FR/' . self::SRI_FILENAME;
        $this->writeSriFile($enUS);
        $this->writeSriFile($frFR);

        $this->plugin->beforeDeploy($this->subject, [
            Options::LANGUAGE => ['en_US'],
        ]);

        $this->assertSriFileNotExists($enUS, 'en_US SRI file must be deleted');
        $this->assertSriFileExists($frFR, 'fr_FR SRI file must be preserved');
    }

    /**
     * Merged-asset cache must be deleted on a full deploy.
     */
    public function testFullDeployDeletesMergedCache(): void
    {
        $merged = '_cache/merged/' . self::SRI_FILENAME;
        $this->writeSriFile($merged);

        $this->plugin->beforeDeploy($this->subject, []);

        $this->assertSriFileNotExists($merged, 'Merged cache must be deleted on full deploy');
    }

    /**
     * Merged-asset cache must be deleted on a partial deploy.
     */
    public function testPartialDeployDeletesMergedCache(): void
    {
        $merged = '_cache/merged/' . self::SRI_FILENAME;
        $blank  = 'frontend/Magento/blank/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($merged);
        $this->writeSriFile($blank);

        $this->plugin->beforeDeploy($this->subject, [
            Options::THEME => ['Magento/luma'],
        ]);

        $this->assertSriFileNotExists($merged, 'Merged cache must be deleted on partial deploy');
        $this->assertSriFileExists($blank, 'Blank SRI file must be preserved');
    }

    /**
     * Partial deploy with a theme that has no deployed files must delete nothing.
     */
    public function testPartialDeployNonExistentThemeDeletesNothing(): void
    {
        $luma = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($luma);

        $this->plugin->beforeDeploy($this->subject, [
            Options::THEME => ['Vendor/nonexistent'],
        ]);

        $this->assertSriFileExists($luma, 'Luma SRI file must be preserved when theme does not match');
    }

    /**
     * REFRESH_CONTENT_VERSION_ONLY must skip deletion entirely.
     */
    public function testRefreshContentVersionOnlySkipsDeletion(): void
    {
        $luma = 'frontend/Magento/luma/en_US/' . self::SRI_FILENAME;
        $this->writeSriFile($luma);

        $this->plugin->beforeDeploy($this->subject, [
            Options::REFRESH_CONTENT_VERSION_ONLY => true,
        ]);

        $this->assertSriFileExists($luma, 'SRI file must not be deleted on version-refresh-only');
    }

    /**
     * @param string $relativePath
     * @return void
     */
    private function writeSriFile(string $relativePath): void
    {
        $this->filesystem
            ->getDirectoryWrite(DirectoryList::STATIC_VIEW)
            ->writeFile($relativePath, '{}');
        $this->createdFiles[] = $relativePath;
    }

    /**
     * @param string $relativePath
     * @param string $message
     * @return void
     */
    private function assertSriFileExists(string $relativePath, string $message = ''): void
    {
        $this->assertTrue(
            $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->isFile($relativePath),
            $message
        );
    }

    /**
     * @param string $relativePath
     * @param string $message
     * @return void
     */
    private function assertSriFileNotExists(string $relativePath, string $message = ''): void
    {
        $this->assertFalse(
            $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->isFile($relativePath),
            $message
        );
    }
}

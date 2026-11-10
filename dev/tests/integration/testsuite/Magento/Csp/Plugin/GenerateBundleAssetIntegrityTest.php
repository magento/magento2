<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Deploy\Service\Bundle;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for GenerateBundleAssetIntegrity plugin exception handling.
 *
 * Tests the afterDeploy plugin method's exception handling paths using actual filesystem operations.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class GenerateBundleAssetIntegrityTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var State
     */
    private State $appState;

    /**
     * @var string
     */
    private string $prevMode;

    /**
     * @var array Directories to restore permissions
     */
    private array $dirsToRestore = [];

    /**
     * @var array Files/directories to cleanup
     */
    private array $filesToCleanup = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->appState = $objectManager->get(State::class);

        $this->prevMode = $this->appState->getMode();
        $this->appState->setMode(State::MODE_PRODUCTION);
        $this->dirsToRestore = [];
        $this->filesToCleanup = [];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        // Restore directory permissions before cleanup
        foreach ($this->dirsToRestore as $relativePath) {
            try {
                if ($staticDir->isExist($relativePath)) {
                    $staticDir->changePermissions($relativePath, 0755);
                }
            } catch (\Exception $e) {
                // Ignore restore errors
            }
        }

        // Clean up test files/directories using filesystem API
        foreach ($this->filesToCleanup as $path) {
            try {
                if ($staticDir->isExist($path)) {
                    $staticDir->delete($path);
                }
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }

        try {
            $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
            $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
                ->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
            $staticDir->create();
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }

        $this->appState->setMode($this->prevMode);

        parent::tearDown();
    }

    /**
     * Test that plugin handles search() exception gracefully.
     *
     * This tests the outer exception path where filesystem search() throws an exception
     * due to directory being completely inaccessible (line 135-139 in GenerateBundleAssetIntegrity).
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @throws LocalizedException
     */
    public function testSearchExceptionHandling(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $plugin = $objectManager->get(GenerateBundleAssetIntegrity::class);
        $design = $objectManager->get(DesignInterface::class);
        $bundleService = $objectManager->get(Bundle::class);
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $area = Area::AREA_FRONTEND;
        $theme = 'Magento/luma';
        $locale = 'en_US';

        $this->appState->setAreaCode($area);
        $design->setDesignTheme($theme, $area);

        $bundleDirPath = $area . '/' . $theme . '/' . $locale . '/' . Bundle::BUNDLE_JS_DIR;
        $staticDir->create($bundleDirPath);

        $staticDir->writeFile($bundleDirPath . '/bundle0.js', '/* Bundle 0 */ console.log("test");');

        // Make directory unreadable to test search() exception handling
        $staticDir->changePermissions($bundleDirPath, 0000);

        // Track bundle directory for permission restoration and cleanup
        $this->dirsToRestore[] = $bundleDirPath;
        $this->filesToCleanup[] = $bundleDirPath;

        // This should not throw exception - plugin catches and logs search() errors
        $plugin->afterDeploy($bundleService, null, $area, $theme, $locale);

        // Verify no exception was thrown and test completes successfully
        $this->assertTrue(true, 'Plugin should handle search exception gracefully without throwing');
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\HashResolver;

use Magento\Deploy\Package\Package;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\UrlInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for HashResolver
 *
 * Tests theme hierarchy traversal and hash resolution fallback logic.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class HashResolverTest extends TestCase
{
    private const SRI_FILENAME = 'sri-hashes.json';
    private const TEST_ASSET_PATH = 'js/test-asset.js';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var HashResolverInterface
     */
    private HashResolverInterface $hashResolver;

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var DesignInterface
     */
    private DesignInterface $design;

    /**
     * @var array
     */
    private array $filesToCleanup = [];

    /**
     * @var string
     */
    private string $prevMode;

    /**
     * @var ThemeInterface|null
     */
    private ?ThemeInterface $lumaTheme = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        $this->design = $objectManager->get(DesignInterface::class);

        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->staticDir->getDriver()->createDirectory($this->staticDir->getAbsolutePath());
        Glob::clearCache();

        $this->hashResolver = $objectManager->create(HashResolverInterface::class);

        $this->filesToCleanup = [];
        $this->ensureDeploymentVersionExists();

        // Load themes
        $themeCollection = $objectManager->create(ThemeCollection::class);
        $this->lumaTheme = $themeCollection->getThemeByFullPath('frontend/Magento/luma');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->filesToCleanup as $file) {
            if ($this->staticDir->isExist($file)) {
                $this->staticDir->delete($file);
            }
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->staticDir->getDriver()->createDirectory($this->staticDir->getAbsolutePath());

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode($this->prevMode);

        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testHashCreatedInCurrentLocaleTheme(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);
        $this->design->setDefaultDesignTheme();

        $context = 'frontend/Magento/luma/en_US';
        $hash = 'sha256-currentlocale';
        $path = $context . '/' . self::TEST_ASSET_PATH;
        $this->createSriHashFile($context, [$path => $hash]);

        $resolvedHash = $this->hashResolver->getHashByPath($path);

        $this->assertEquals($hash, $resolvedHash, 'Should resolve hash from current locale/theme');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code fr_FR
     */
    public function testHashCreatedInDefaultLocale(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Only create hash in default locale
        $context = 'frontend/Magento/luma/' . Package::BASE_LOCALE;
        $hash = 'sha256-defaultlocale';
        $path = $context . '/' . self::TEST_ASSET_PATH;
        $this->createSriHashFile($context, [$path => $hash]);

        $resolvedHash = $this->hashResolver->getHashByPath($path);

        $this->assertEquals($hash, $resolvedHash, 'Should fall back to default locale');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testFallsBackToBaseArea(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Create hash ONLY in base area
        $baseContext = 'base/Magento/base/' . Package::BASE_LOCALE;
        $hash = 'sha256-basearea';
        $path = $baseContext . '/' . self::TEST_ASSET_PATH;
        $this->createSriHashFile($baseContext, [$path => $hash]);

        $resolvedHash = $this->hashResolver->getHashByPath($path);

        $this->assertEquals($hash, $resolvedHash, 'Should fall back to base area');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testThemeHierarchyPriority(): void
    {
        // Luma extends Blank
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Context 1: Current theme + current locale (highest priority)
        $this->createSriHashFile('frontend/Magento/luma/en_US', [
            'frontend/Magento/luma/en_US/js/luma-specific.js' => 'sha256-luma-specific'
        ]);

        // Context 2: Current theme + default locale (fallback)
        $this->createSriHashFile('frontend/Magento/luma/default', [
            'frontend/Magento/luma/default/js/luma-default.js' => 'sha256-luma-default'
        ]);

        // Context 3: Base area (always included)
        $this->createSriHashFile('base/Magento/base/default', [
            'base/Magento/base/default/js/base-only.js' => 'sha256-base-only'
        ]);

        // Active-theme and base contexts are reachable on any deploy strategy
        $this->assertEquals(
            'sha256-luma-specific',
            $this->hashResolver->getHashByPath('frontend/Magento/luma/en_US/js/luma-specific.js'),
            'Should find hash in current theme + locale'
        );
        $this->assertEquals(
            'sha256-luma-default',
            $this->hashResolver->getHashByPath('frontend/Magento/luma/default/js/luma-default.js'),
            'Should find hash in current theme + default locale'
        );
        $this->assertEquals(
            'sha256-base-only',
            $this->hashResolver->getHashByPath('base/Magento/base/default/js/base-only.js'),
            'Should find hash in base area'
        );

        // getAllHashes() covers base + active theme only (no parent themes on non-compact deploy)
        $this->assertCount(
            3,
            $this->hashResolver->getAllHashes(),
            'getAllHashes should return base + current theme hashes only'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     */
    public function testWithMissingAllHashFiles(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Don't create any hash files
        $resolvedHash = $this->hashResolver->getHashByPath(self::TEST_ASSET_PATH);

        $this->assertNull($resolvedHash, 'Should return null when no hash files exist');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testWithCorruptedFileInHierarchy(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Create corrupted file in current locale
        $currentContext = 'frontend/Magento/luma/en_US';
        $this->createCorruptedSriHashFile($currentContext);

        // Create valid file in default locale
        $defaultContext = 'frontend/Magento/luma/default';
        $hash = 'sha256-valid';

        // Use AssetRepository to get the proper asset URL format
        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->get(\Magento\Framework\View\Asset\Repository::class);
        $validAsset = $assetRepo->createAsset('js/test.js');
        $validAssetPath = $validAsset->getUrl();

        $this->createSriHashFile($defaultContext, [$validAssetPath => $hash]);

        // Verify the hash from the valid file is retrievable
        $resolvedHash = $this->hashResolver->getHashByPath($validAssetPath);
        $this->assertEquals($hash, $resolvedHash, 'Should retrieve hash from valid JSON file');

        // Verify getAllHashes() only returns hashes from valid files (corrupted file is skipped)
        $allHashes = $this->hashResolver->getAllHashes();
        $this->assertGreaterThanOrEqual(1, count($allHashes), 'Should only include hashes from valid JSON files');
        $this->assertContains($hash, array_values($allHashes), 'Should contain the valid hash');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testGetHashByPathReturnsCorrectFormat(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        $context = 'frontend/Magento/luma/en_US';
        $hash = 'sha256-ABC123def456+/=';
        $this->createSriHashFile($context, [self::TEST_ASSET_PATH => $hash]);

        $resolvedHash = $this->hashResolver->getHashByPath(self::TEST_ASSET_PATH);

        $this->assertStringStartsWith('sha256-', $resolvedHash);
        $this->assertEquals($hash, $resolvedHash);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testWithDifferentAssetPaths(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        $context = 'frontend/Magento/luma/en_US';
        $hashes = [
            'js/file1.js' => 'sha256-hash1',
            'js/subfolder/file2.js' => 'sha256-hash2',
            'Magento_Checkout/js/checkout.js' => 'sha256-hash3',
        ];
        $this->createSriHashFile($context, $hashes);

        foreach ($hashes as $path => $expectedHash) {
            $resolvedHash = $this->hashResolver->getHashByPath($path);
            $this->assertEquals($expectedHash, $resolvedHash, "Hash for {$path} should match");
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testWithNonExistentAssetPath(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        $context = 'frontend/Magento/luma/en_US';
        $this->createSriHashFile($context, ['js/exists.js' => 'sha256-exists']);

        $resolvedHash = $this->hashResolver->getHashByPath('js/does-not-exist.js');

        $this->assertNull($resolvedHash, 'Should return null for non-existent asset');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testAdminhtmlAreaResolution(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setAreaCode(Area::AREA_ADMINHTML);

        $themeCollection = $objectManager->create(ThemeCollection::class);
        $backendTheme = $themeCollection->getThemeByFullPath('adminhtml/Magento/backend');
        $this->design->setDesignTheme($backendTheme, Area::AREA_ADMINHTML);

        // Create hash in adminhtml area
        $context = 'adminhtml/Magento/backend/en_US';
        $hash = 'sha256-adminhtml';
        $this->createSriHashFile($context, [self::TEST_ASSET_PATH => $hash]);

        $resolvedHash = $this->hashResolver->getHashByPath(self::TEST_ASSET_PATH);

        $this->assertEquals($hash, $resolvedHash, 'Should resolve hash in adminhtml area');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testWithEmptyHashFile(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Create empty JSON object in current context
        $context = 'frontend/Magento/luma/en_US';
        $this->createSriHashFile($context, []);

        // Request hash for any asset
        $resolvedHash = $this->hashResolver->getHashByPath(self::TEST_ASSET_PATH);

        $this->assertNull($resolvedHash, 'Should return null when hash file is empty and asset not found');
    }

    /**
     * Test that getAllHashes() returns hashes from base contexts and the current theme only,
     * and does not include hashes from parent themes in the inheritance chain.
     *
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testGetAllHashesOnlyIncludesBaseAndCurrentThemeContexts(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        // Base context — included
        $this->createSriHashFile('base/Magento/base/default', [
            'js/base-global.js' => 'sha256-base-global',
        ]);

        // Area-level base context — included
        $this->createSriHashFile('frontend/Magento/base/default', [
            'js/frontend-base.js' => 'sha256-frontend-base',
        ]);

        // Current theme default locale — included
        $this->createSriHashFile('frontend/Magento/luma/default', [
            'js/luma-shared.js' => 'sha256-luma-shared',
        ]);

        // Current theme specific locale — included
        $this->createSriHashFile('frontend/Magento/luma/en_US', [
            'js/luma-locale.js' => 'sha256-luma-locale',
        ]);

        // Parent theme — must NOT be included in getAllHashes()
        $this->createSriHashFile('frontend/Magento/blank/en_US', [
            'js/blank-only.js' => 'sha256-blank-only',
        ]);

        // Merged assets cache — included
        $mergedFile = '_cache/merged/sri-hashes.json';
        $this->filesToCleanup[] = $mergedFile;
        if (!$this->staticDir->isExist('_cache/merged')) {
            $this->staticDir->create('_cache/merged');
        }
        $this->staticDir->writeFile($mergedFile, json_encode(['js/merged.js' => 'sha256-merged']));

        $baseUrl = Bootstrap::getObjectManager()->get(UrlInterface::class)
            ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC]);

        $allHashes = $this->hashResolver->getAllHashes();

        $this->assertArrayHasKey(
            $baseUrl . 'js/base-global.js',
            $allHashes,
            'base/Magento/base/default hashes must be included'
        );
        $this->assertArrayHasKey(
            $baseUrl . 'js/frontend-base.js',
            $allHashes,
            'frontend/Magento/base/default hashes must be included'
        );
        $this->assertArrayHasKey(
            $baseUrl . 'js/luma-shared.js',
            $allHashes,
            'Current theme default-locale hashes must be included'
        );
        $this->assertArrayHasKey(
            $baseUrl . 'js/luma-locale.js',
            $allHashes,
            'Current theme specific-locale hashes must be included'
        );
        $this->assertArrayHasKey(
            $baseUrl . 'js/merged.js',
            $allHashes,
            'Merged hashes (_cache/merged/sri-hashes.json) must be included'
        );
        $this->assertArrayNotHasKey(
            $baseUrl . 'js/blank-only.js',
            $allHashes,
            'Parent theme hashes must NOT be included in getAllHashes()'
        );
    }

    /**
     * Test that getAllHashes() returns no duplicate URL keys even when the same asset path
     * exists in multiple valid contexts (e.g. luma/default and luma/en_US).
     * The later context in the load order (en_US) must win.
     *
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testGetAllHashesHasNoDuplicateKeys(): void
    {
        $this->design->setDesignTheme($this->lumaTheme, Area::AREA_FRONTEND);

        $sharedPath = 'js/shared-asset.js';

        // Same path written to both default-locale and specific-locale contexts.
        // en_US is loaded after default in getAllHashes(), so its hash must win.
        $this->createSriHashFile('frontend/Magento/luma/default', [
            $sharedPath => 'sha256-from-default',
        ]);
        $this->createSriHashFile('frontend/Magento/luma/en_US', [
            $sharedPath => 'sha256-from-en-US',
        ]);

        $baseUrl = Bootstrap::getObjectManager()->get(UrlInterface::class)
            ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC]);
        $expectedKey = $baseUrl . $sharedPath;

        $allHashes = $this->hashResolver->getAllHashes();

        $keys = array_keys($allHashes);
        $this->assertCount(
            count(array_unique($keys)),
            $keys,
            'getAllHashes() must not return duplicate URL keys'
        );

        $this->assertArrayHasKey($expectedKey, $allHashes, 'Shared asset path must appear exactly once');
        $this->assertSame(
            'sha256-from-en-US',
            $allHashes[$expectedKey],
            'The locale-specific (en_US) hash must override the default-locale hash'
        );
    }

    /**
     * When the active theme is Magento/base in the frontend area, the context
     * 'frontend/Magento/base/default' is produced by both getBaseContexts() and the
     * theme-traversal loop. This test verifies that the duplicate context is deduplicated
     * so each asset hash appears exactly once in the result.
     *
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testGetHashByPathDeduplicatesDuplicateContexts(): void
    {
        $themeCollection = Bootstrap::getObjectManager()->create(ThemeCollection::class);
        $baseTheme = $themeCollection->getThemeByFullPath('frontend/Magento/base');
        $this->design->setDesignTheme($baseTheme, Area::AREA_FRONTEND);

        // This context is generated by BOTH getBaseContexts() and the theme loop,
        // so without deduplication it would be loaded twice.
        $this->createSriHashFile('frontend/Magento/base/default', [
            'js/base-theme-asset.js' => 'sha256-base-theme',
        ]);

        $baseUrl = Bootstrap::getObjectManager()->get(UrlInterface::class)
            ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC]);

        // getHashByPath() must return the hash exactly once (not fail or duplicate)
        $hash = $this->hashResolver->getHashByPath('js/base-theme-asset.js');
        $this->assertSame('sha256-base-theme', $hash, 'Hash must be resolved despite duplicate context');

        // getAllHashes() must not produce duplicate URL keys
        $allHashes = $this->hashResolver->getAllHashes();
        $keys = array_keys($allHashes);
        $this->assertCount(
            count(array_unique($keys)),
            $keys,
            'getAllHashes() must not produce duplicate URL keys when a context appears twice'
        );

        $expectedKey = $baseUrl . 'js/base-theme-asset.js';
        $this->assertArrayHasKey($expectedKey, $allHashes, 'Hash from the duplicated context must still be present');
        $this->assertSame('sha256-base-theme', $allHashes[$expectedKey]);
    }

    /**
     * Create SRI hash file for testing
     *
     * @param string $context
     * @param array $hashes
     * @return void
     */
    private function createSriHashFile(string $context, array $hashes): void
    {
        $filePath = $context . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        if (!$this->staticDir->isExist($context)) {
            $this->staticDir->create($context);
        }

        $this->staticDir->writeFile($filePath, json_encode($hashes));
    }

    /**
     * Create corrupted SRI hash file for testing
     *
     * @param string $context
     * @return void
     */
    private function createCorruptedSriHashFile(string $context): void
    {
        $filePath = $context . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        if (!$this->staticDir->isExist($context)) {
            $this->staticDir->create($context);
        }

        $this->staticDir->writeFile($filePath, '{invalid json syntax {{');
    }

    /**
     * Ensures deployment version file exists for asset URL generation
     *
     * @return void
     */
    private function ensureDeploymentVersionExists(): void
    {
        try {
            $versionFile = 'deployed_version.txt';

            if (!$this->staticDir->isExist($versionFile)) {
                $this->staticDir->writeFile($versionFile, (string)time());
            }
        } catch (\Exception $e) {
            // Silently fail - if this doesn't work, tests will show the error
        }
    }
}

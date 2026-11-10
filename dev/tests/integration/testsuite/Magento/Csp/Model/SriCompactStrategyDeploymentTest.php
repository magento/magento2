<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\View\Asset\RepositoryMap;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use PHPUnit\Framework\TestCase;

/**
 * Integration test verifying SRI files with COMPACT deployment strategy.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Deploy/_files/theme.php
 * @group slow
 * @group sri_deployment
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriCompactStrategyDeploymentTest extends TestCase
{
    /**
     * constant for SRI filename
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * constant for maximum file size in KB
     */
    private const MAX_FILE_SIZE_KB = 500;

    /**
     * constant for minimum file size in bytes
     */
    private const MIN_FILE_SIZE_BYTES = 100;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $staticDir;

    /**
     * @var string
     */
    private $prevMode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode($this->prevMode);

        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->staticDir->getDriver()->createDirectory($this->staticDir->getAbsolutePath());

        parent::tearDown();
    }

    /**
     * Test SRI files with COMPACT strategy.
     *
     * @magentoDbIsolation disabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCompactStrategyCreatesValidSriFiles(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $deployService = $objectManager->create(DeployStaticContent::class, ['logger' => $logger]);

        $options = [
            Options::DRY_RUN => false,
            Options::NO_JAVASCRIPT => false,
            Options::NO_JS_BUNDLE => true,
            Options::NO_CSS => true,
            Options::NO_LESS => true,
            Options::NO_IMAGES => true,
            Options::NO_FONTS => true,
            Options::NO_HTML => true,
            Options::NO_MISC => true,
            Options::NO_HTML_MINIFY => true,
            Options::AREA => ['frontend'],
            Options::EXCLUDE_AREA => ['none'],
            Options::THEME => ['Magento/zoom1', 'Magento/zoom2'],
            Options::EXCLUDE_THEME => ['none'],
            Options::LANGUAGE => ['en_US', 'de_DE'],
            Options::EXCLUDE_LANGUAGE => ['none'],
            Options::JOBS_AMOUNT => 4,
            Options::SYMLINK_LOCALE => false,
            Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_COMPACT,
        ];

        $deployService->deploy($options);

        $expectedPaths = [
            'frontend/Magento/zoom1/en_US/' . self::SRI_FILENAME,
            'frontend/Magento/zoom1/de_DE/' . self::SRI_FILENAME,
            'frontend/Magento/zoom2/en_US/' . self::SRI_FILENAME,
            'frontend/Magento/zoom2/de_DE/' . self::SRI_FILENAME,
        ];

        foreach ($expectedPaths as $path) {
            // Assert file exists at correct location
            $this->assertTrue($this->staticDir->isExist($path), "SRI file should exist at: {$path}");

            // Assert file size is reasonable
            $stat = $this->staticDir->stat($path);
            $this->assertGreaterThan(self::MIN_FILE_SIZE_BYTES, $stat['size'], "File too small: {$path}");
            $this->assertLessThan(self::MAX_FILE_SIZE_KB * 1024, $stat['size'], "File too large: {$path}");

            // Assert valid JSON content
            $content = $this->staticDir->readFile($path);
            $data = json_decode($content, true);
            $this->assertIsArray($data, "Invalid JSON at: {$path}");
            $this->assertNotEmpty($data, "Empty content at: {$path}");
            $this->assertStringStartsWith('sha256-', reset($data), "Invalid hash format at: {$path}");
        }

        // Assert old area-level file NOT created
        $this->assertFalse($this->staticDir->isExist('frontend/' . self::SRI_FILENAME));

        // === Compact Strategy Pattern Validation ===
        // Use isExist() checks for Adobe patterns (Pattern 1, 3, 5)

        // Pattern 1: Global base default
        $pattern1 = 'base/Magento/base/default/' . self::SRI_FILENAME;
        $pattern1Exists = $this->staticDir->isExist($pattern1);

        // Pattern 3: Frontend base default
        $pattern3 = 'frontend/Magento/base/default/' . self::SRI_FILENAME;
        $pattern3Exists = $this->staticDir->isExist($pattern3);

        // Pattern 5: Theme defaults
        $pattern5_zoom1 = 'frontend/Magento/zoom1/default/' . self::SRI_FILENAME;
        $pattern5_zoom1Exists = $this->staticDir->isExist($pattern5_zoom1);

        $pattern5_zoom2 = 'frontend/Magento/zoom2/default/' . self::SRI_FILENAME;
        $pattern5_zoom2Exists = $this->staticDir->isExist($pattern5_zoom2);

        // Pattern 6: Already validated above (4 theme+locale combinations)

        // Compact MUST have at least one /default/ directory file
        $hasDefaultFiles = $pattern1Exists || $pattern3Exists || $pattern5_zoom1Exists || $pattern5_zoom2Exists;
        $this->assertTrue(
            $hasDefaultFiles,
            'Compact strategy MUST create at least one /default/ directory file for shared assets'
        );

        $resultMapPaths = [
            'frontend/Magento/zoom1/en_US/' . RepositoryMap::RESULT_MAP_NAME,
            'frontend/Magento/zoom1/de_DE/' . RepositoryMap::RESULT_MAP_NAME,
            'frontend/Magento/zoom2/en_US/' . RepositoryMap::RESULT_MAP_NAME,
            'frontend/Magento/zoom2/de_DE/' . RepositoryMap::RESULT_MAP_NAME,
        ];
        foreach ($resultMapPaths as $resultMapPath) {
            $this->assertTrue(
                $this->staticDir->isExist($resultMapPath),
                "Compact deploy must produce {$resultMapPath}"
            );
        }

        // === QA regression check: parent-theme hashes must appear in getAllHashes() ===
        // zoom2 extends zoom1. On compact deploy the HashResolver must walk the full
        // parent chain, so zoom1 hashes must be visible when zoom2 is the active theme.
        // This directly reproduces the QA finding where blank/default was absent from
        // window.sriHashes after a compact SCD run.
        $themeCollection = $objectManager->create(ThemeCollection::class);
        $zoom2Theme = $themeCollection->getThemeByFullPath('frontend/Magento/zoom2');
        $this->assertNotNull($zoom2Theme, 'zoom2 theme must be registered');

        $design = $objectManager->get(DesignInterface::class);
        $design->setDesignTheme($zoom2Theme, 'frontend');

        // Create a fresh resolver with a fresh repository pool so getAllHashes() reads
        // from disk (not in-memory deploy-time state).
        Glob::clearCache();
        $freshPool = $objectManager->create(SubresourceIntegrityRepositoryPool::class);
        $hashResolver = $objectManager->create(
            HashResolverInterface::class,
            ['repositoryPool' => $freshPool]
        );
        $allHashes = $hashResolver->getAllHashes();

        // Verify parent theme (zoom1) hashes appear in getAllHashes() regardless of locale.
        // The locale changes during deploy (iterating en_US, de_DE, etc.) so we cannot
        // assert a specific locale context — just that zoom1 is represented at all.
        $zoom1Keys = array_filter(
            array_keys($allHashes),
            static fn(string $hashUrl): bool => str_contains($hashUrl, 'Magento/zoom1/')
        );
        $this->assertNotEmpty(
            $zoom1Keys,
            'Parent theme (zoom1) hashes must appear in getAllHashes() when zoom2 is active — QA regression'
        );
    }
}

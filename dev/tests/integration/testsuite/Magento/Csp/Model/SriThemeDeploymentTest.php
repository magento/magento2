<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;

/**
 * Integration test verifying SRI hash generation for theme JS files.
 *
 * Uses existing zoom1 theme fixture from Magento/Deploy tests.
 * Deploys static content once and verifies SRI hashes.
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Deploy/_files/theme.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriThemeDeploymentTest extends TestCase
{
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * Test theme from Deploy fixtures
     */
    private const TEST_THEME = 'Magento/zoom1';

    /**
     * Test locale (compact strategy uses 'default')
     */
    private const TEST_LOCALE = 'default';

    /**
     * Known JS file in zoom1 theme
     */
    private const TEST_JS_FILE = 'js/file1.js';

    /**
     * @var bool
     */
    private static $deployed = false;

    /**
     * @var string|null
     */
    private static $prevMode;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteInterface
     */
    private $staticDir;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->hashGenerator = $objectManager->get(HashGenerator::class);

        // Deploy only once for all tests
        if (!self::$deployed) {
            $this->deployStaticContent();
            self::$deployed = true;
        }
    }

    /**
     * Deploy static content for tests.
     *
     * @return void
     */
    private function deployStaticContent(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Set production mode
        self::$prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        // Clean up before deployment
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);

        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $deployService = $objectManager->create(
            DeployStaticContent::class,
            ['logger' => $logger]
        );

        $deployOptions = [
            Options::DRY_RUN => false,
            Options::NO_JAVASCRIPT => false,
            Options::NO_JS_BUNDLE => true,  // Skip bundling - we're testing SRI not bundles
            Options::NO_CSS => true,  // Skip CSS to speed up tests
            Options::NO_LESS => true,
            Options::NO_IMAGES => true,
            Options::NO_FONTS => true,
            Options::NO_HTML => true,
            Options::NO_MISC => true,
            Options::NO_HTML_MINIFY => true,
            Options::AREA => ['frontend'],
            Options::EXCLUDE_AREA => ['none'],
            Options::THEME => ['Magento/zoom1'],
            Options::EXCLUDE_THEME => ['none'],
            Options::LANGUAGE => ['en_US'],
            Options::EXCLUDE_LANGUAGE => ['none'],
            Options::JOBS_AMOUNT => 1,
            Options::SYMLINK_LOCALE => false,
            Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_COMPACT,
            'no-parent' => false,
        ];

        $deployService->deploy($deployOptions);
    }

    /**
     * Clean up after all tests.
     */
    public static function tearDownAfterClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        if (self::$prevMode !== null) {
            $objectManager->get(State::class)->setMode(self::$prevMode);
        }

        // Clean up static directory
        $filesystem = $objectManager->get(Filesystem::class);
        $filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $staticDir->getDriver()->createDirectory($staticDir->getAbsolutePath());

        self::$deployed = false;
        self::$prevMode = null;
    }

    /**
     * Get path to sri-hashes.json for test theme.
     *
     * @return string
     */
    private function getSriHashesPath(): string
    {
        return 'frontend/' . self::TEST_THEME . '/' . self::TEST_LOCALE . '/' . self::SRI_FILENAME;
    }

    /**
     * Get path to test JS file.
     *
     * @return string
     */
    private function getTestJsPath(): string
    {
        return 'frontend/' . self::TEST_THEME . '/' . self::TEST_LOCALE . '/' . self::TEST_JS_FILE;
    }

    /**
     * Test that sri-hashes.json is generated during deployment.
     */
    public function testSriHashesJsonIsGenerated(): void
    {
        $this->assertTrue(
            $this->staticDir->isExist($this->getSriHashesPath()),
            'sri-hashes.json should be generated during deployment'
        );
    }

    /**
     * Test that sri-hashes.json contains valid JSON with entries.
     */
    public function testSriHashesJsonIsValid(): void
    {
        $content = $this->staticDir->readFile($this->getSriHashesPath());
        $hashes = $this->serializer->unserialize($content);

        $this->assertIsArray($hashes, 'sri-hashes.json should contain a JSON array');
        $this->assertNotEmpty($hashes, 'sri-hashes.json should not be empty');
    }

    /**
     * Test that custom theme JS file has correct hash in sri-hashes.json.
     */
    public function testCustomThemeJsHasCorrectHash(): void
    {
        $jsPath = $this->getTestJsPath();
        $sriHashesPath = $this->getSriHashesPath();

        // Verify JS file was deployed
        $this->assertTrue(
            $this->staticDir->isExist($jsPath),
            'Theme JS file should be deployed'
        );

        // Load sri-hashes.json
        $hashes = $this->serializer->unserialize(
            $this->staticDir->readFile($sriHashesPath)
        );

        // Verify our JS file has an entry
        $this->assertArrayHasKey(
            $jsPath,
            $hashes,
            'sri-hashes.json should contain entry for custom theme JS file'
        );

        // Verify hash matches actual file content
        $actualContent = $this->staticDir->readFile($jsPath);
        $expectedHash = $this->hashGenerator->generate($actualContent);

        $this->assertEquals(
            $expectedHash,
            $hashes[$jsPath],
            'Hash in sri-hashes.json should match actual file content'
        );
    }

    /**
     * Test that all hashes in sri-hashes.json have valid SHA-256 format.
     */
    public function testAllHashesHaveCorrectFormat(): void
    {
        $hashes = $this->serializer->unserialize(
            $this->staticDir->readFile($this->getSriHashesPath())
        );

        foreach ($hashes as $path => $hash) {
            $this->assertStringStartsWith(
                'sha256-',
                $hash,
                "Hash for {$path} should start with sha256-"
            );

            // Verify base64 format
            $base64Part = substr($hash, 7);
            $this->assertMatchesRegularExpression(
                '/^[A-Za-z0-9+\/]+=*$/',
                $base64Part,
                "Hash for {$path} should have valid base64 content"
            );
        }
    }

    /**
     * Test that multiple JS files in theme have hashes.
     */
    public function testMultipleJsFilesHaveHashes(): void
    {
        $hashes = $this->serializer->unserialize(
            $this->staticDir->readFile($this->getSriHashesPath())
        );

        // zoom1 theme has file1.js, file2.js, file3.js, file4.js
        $expectedFiles = ['js/file1.js', 'js/file2.js', 'js/file3.js', 'js/file4.js'];
        $themePath = 'frontend/' . self::TEST_THEME . '/' . self::TEST_LOCALE . '/';

        foreach ($expectedFiles as $file) {
            $fullPath = $themePath . $file;
            $this->assertArrayHasKey(
                $fullPath,
                $hashes,
                "sri-hashes.json should contain entry for {$file}"
            );
        }
    }

    /**
     * Test that old area-level SRI file is NOT created at frontend/sri-hashes.json.
     */
    public function testOldAreaLevelFileNotCreated(): void
    {
        // Old-style area-level files should NOT exist
        $this->assertFalse(
            $this->staticDir->isExist('frontend/' . self::SRI_FILENAME),
            'Old-style area-level SRI file should not be created'
        );
    }
}

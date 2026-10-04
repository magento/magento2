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
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test verifying SRI files with QUICK deployment strategy.
 *
 * @magentoAppArea frontend
 * @magentoDataFixture Magento/Deploy/_files/theme.php
 * @group slow
 * @group sri_deployment
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SriQuickStrategyDeploymentTest extends TestCase
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
     * Test SRI files with QUICK strategy - parallel deployment.
     *
     * @magentoDbIsolation disabled
     */
    public function testQuickStrategyCreatesValidSriFiles(): void
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
            Options::JOBS_AMOUNT => 1,
            Options::SYMLINK_LOCALE => false,
            Options::STRATEGY => DeployStrategyFactory::DEPLOY_STRATEGY_QUICK,
            Options::NO_PARENT => false,
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
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

use Magento\Csp\Block\Sri\Hashes;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Integration test validating that JS assets are minified with different deployment strategies.
 *
 * Tests that when minification is enabled without bundling,
 * all JS assets in the SRI repository have .min.js extensions
 * across different deployment strategies (quick, standard, compact).
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @group sri_renderer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriMinificationTest extends TestCase
{
    /**
     * @var State
     */
    private State $appState;

    /**
     * @var DesignInterface
     */
    private DesignInterface $design;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var string
     */
    private string $prevMode;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Hashes
     */
    private Hashes $hashesBlock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->appState = $objectManager->get(State::class);
        $this->design = $objectManager->get(DesignInterface::class);
        $this->request = $objectManager->get(Http::class);
        $this->hashesBlock = $objectManager->get(Hashes::class);
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);

        // Enable production mode for minified files
        $this->prevMode = $this->appState->getMode();
        $this->appState->setMode(State::MODE_PRODUCTION);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->appState->setMode($this->prevMode);

        // Clean up both static content and preprocessed view files to ensure clean state between tests
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);

        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $staticDir->create();

        parent::tearDown();
    }

    /**
     * Test that all JS assets are minified when using quick strategy.
     * Tests with bundling and merging disabled, using quick strategy.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 0
     * @magentoConfigFixture current_store dev/static/sign 1
     */
    public function testMinificationWithQuickStrategy(): void
    {
        $this->assertMinificationWorks('quick');
    }

    /**
     * Test that all JS assets are minified when using standard strategy.
     * Tests with bundling and merging disabled, using standard strategy.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 0
     * @magentoConfigFixture current_store dev/static/sign 1
     */
    public function testMinificationWithStandardStrategy(): void
    {
        $this->assertMinificationWorks('standard');
    }

    /**
     * Test that all JS assets are minified when using compact strategy.
     * Tests with bundling and merging disabled, using compact strategy.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 0
     * @magentoConfigFixture current_store dev/static/sign 1
     */
    public function testMinificationWithCompactStrategy(): void
    {
        $this->assertMinificationWorks('compact');
    }

    /**
     * Assert that minification works for a given strategy.
     *
     * @param string $strategy The deployment strategy to test
     * @return void
     * @throws LocalizedException
     */
    private function assertMinificationWorks(string $strategy): void
    {
        // Set up area and design
        $this->appState->setAreaCode(Area::AREA_FRONTEND);
        $this->design->setDesignTheme('Magento/luma', Area::AREA_FRONTEND);
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        // Deploy static content with minification
        try {
            $this->deployStaticContent($strategy);
        } catch (FileSystemException $e) {
            // Wait for filesystem to flush before retrying
            usleep(100000);
            $this->deployStaticContent($strategy);
        }

        // Get all deployed assets with SRI hashes from the repository
        $serializedHashes = $this->hashesBlock->getSerialized();
        $deployedAssets = $this->serializer->unserialize($serializedHashes);

        $this->assertNotEmpty($deployedAssets, 'Should have deployed JS assets');

        // Third-party libraries that may not be minified by quick/standard strategies
        $thirdPartyLibraries = ['hugerte/', 'tiny_mce/', 'tinymce/'];

        // Check that all deployed JS assets are minified
        $nonMinifiedScripts = [];
        foreach (array_keys($deployedAssets) as $url) {

            // Skip third-party libraries as they may not be minified by all strategies
            $isThirdParty = false;
            foreach ($thirdPartyLibraries as $library) {
                if (strpos($url, '/' . $library) !== false) {
                    $isThirdParty = true;
                    break;
                }
            }

            if ($isThirdParty) {
                continue;
            }

            // Check if the file has .min.js extension
            if (strpos($url, '.min.js') === false) {
                $nonMinifiedScripts[] = $url;
            }
        }

        $this->assertEmpty(
            $nonMinifiedScripts,
            sprintf(
                'All JS assets should be minified (.min.js) when using %s strategy. Non-minified: %s',
                $strategy,
                implode(', ', $nonMinifiedScripts)
            )
        );
    }

    /**
     * Deploy static content.
     *
     * @param string $strategy Deployment strategy
     * @return void
     * @throws LocalizedException
     */
    private function deployStaticContent(string $strategy): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $deployService = $objectManager->get(DeployStaticContent::class);

        $options = [
            Options::STRATEGY => $strategy,
            Options::AREA => ['frontend', 'base'],
            Options::EXCLUDE_AREA => ['none'],
            Options::THEME => ['Magento/luma'],
            Options::EXCLUDE_THEME => ['none'],
            Options::LANGUAGE => ['en_US'],
            Options::EXCLUDE_LANGUAGE => ['none'],
            Options::JOBS_AMOUNT => 1,
            Options::NO_JAVASCRIPT => false,
            Options::NO_JS_BUNDLE => true,
            Options::NO_CSS => true,
            Options::NO_LESS => true,
            Options::NO_IMAGES => true,
            Options::NO_FONTS => true,
            Options::NO_HTML => true,
            Options::NO_MISC => true,
            Options::NO_HTML_MINIFY => true,
            Options::NO_PARENT => true,
        ];

        $deployService->deploy($options);
    }
}

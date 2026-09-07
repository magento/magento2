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
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Integration test validating that JS assets are minified with merging configuration enabled.
 *
 * Tests that when minification and merging are enabled with different deployment strategies
 * (quick, standard, compact), individual JS assets are properly minified with .min.js extensions.
 *
 * Note: Merged files (_cache/merged/*.min.js) are created at runtime when pages are rendered,
 * not during static content deployment.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 * @group sri_renderer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriMinificationMergingTest extends TestCase
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

        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);

        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $staticDir->create();

        parent::tearDown();
    }

    /**
     * Test that JS assets are minified when merging configuration is enabled with quick strategy.
     * Tests with merging enabled, bundling disabled, using quick strategy.
     *
     * Note: Merged files are created at runtime, not during deployment.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @covers \Magento\Csp\Plugin\GenerateMergedAssetIntegrity::afterMerge
     */
    public function testMinificationWithMergingConfigAndQuickStrategy(): void
    {
        $this->assertMinificationAndMerging('quick');
    }

    /**
     * Test that JS assets are minified when merging configuration is enabled with standard strategy.
     * Tests with merging enabled, bundling disabled, using standard strategy.
     *
     * Note: Merged files are created at runtime, not during deployment.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @covers \Magento\Csp\Plugin\GenerateMergedAssetIntegrity::afterMerge
     */
    public function testMinificationWithMergingConfigAndStandardStrategy(): void
    {
        $this->assertMinificationAndMerging('standard');
    }

    /**
     * Test that JS assets are minified when merging configuration is enabled with compact strategy.
     * Tests with merging enabled, bundling disabled, using compact strategy.
     *
     * Note: Merged files are created at runtime, not during deployment.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/minify_files 1
     * @magentoConfigFixture current_store dev/js/enable_js_bundling 0
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @covers \Magento\Csp\Plugin\GenerateMergedAssetIntegrity::afterMerge
     */
    public function testMinificationWithMergingConfigAndCompactStrategy(): void
    {
        $this->assertMinificationAndMerging('compact');
    }

    /**
     * Assert that minification works when merging configuration is enabled for a given strategy.
     *
     * @param string $strategy The deployment strategy to test
     * @return void
     * @throws LocalizedException
     */
    private function assertMinificationAndMerging(string $strategy): void
    {
        $this->appState->setAreaCode(Area::AREA_FRONTEND);
        $this->design->setDesignTheme('Magento/luma', Area::AREA_FRONTEND);
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        try {
            $this->deployStaticContent($strategy);
        } catch (FileSystemException $e) {
            // Wait for filesystem to flush before retrying
            usleep(100000);
            $this->deployStaticContent($strategy);
        }

        $serializedHashes = $this->hashesBlock->getSerialized();
        $deployedAssets = $this->serializer->unserialize($serializedHashes);

        $this->assertNotEmpty($deployedAssets, 'Should have deployed JS assets');

        // Third-party libraries may not be minified by all strategies
        $thirdPartyLibraries = ['hugerte/', 'tiny_mce/', 'tinymce/'];

        $nonMinifiedScripts = [];
        foreach (array_keys($deployedAssets) as $url) {
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

        $objectManager = Bootstrap::getObjectManager();
        $assetConfig = $objectManager->get(\Magento\Framework\View\Asset\ConfigInterface::class);
        $this->assertTrue(
            $assetConfig->isMergeJsFiles(),
            'JS file merging should be enabled in configuration'
        );

        $this->triggerAssetMergingAndVerify($strategy);
    }

    /**
     * Trigger asset merging by directly calling FileExists::merge() to trigger the plugin.
     *
     * This test verifies that the GenerateMergedAssetIntegrity plugin works correctly:
     * - When FileExists::merge() is called, the plugin's afterMerge() runs
     * - The plugin generates SRI hash for the merged file
     * - The plugin saves the hash to the SubresourceIntegrityRepository
     *
     * @param string $strategy
     * @return void
     * @throws LocalizedException
     */
    private function triggerAssetMergingAndVerify(string $strategy): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->get(Repository::class);

        $assets = [];
        $assetPaths = [
            'mage/cookies.js',
            'mage/common.js',
            'mage/translate.js',
        ];

        foreach ($assetPaths as $assetPath) {
            try {
                $asset = $assetRepo->createAsset($assetPath);
                if ($asset) {
                    $assets[] = $asset;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($assets)) {
            $this->markTestSkipped('No JS assets found to merge');
        }

        $mergedAsset = $assetRepo->createArbitrary(
            'test-merged-' . hash('sha256', implode('|', $assetPaths)) . '.min.js',
            '_cache/merged'
        );

        $fileExistsStrategy = $objectManager->get(FileExists::class);

        // Triggers GenerateMergedAssetIntegrity::afterMerge() plugin
        $fileExistsStrategy->merge($assets, $mergedAsset);

        $staticDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $mergedAssetPath = $mergedAsset->getPath();
        $this->assertTrue(
            $staticDir->isFile($mergedAssetPath),
            sprintf('Merged file should exist on filesystem: %s', $mergedAssetPath)
        );

        $this->assertStringContainsString(
            '.min.js',
            $mergedAssetPath,
            'Merged file should have .min.js extension when minification is enabled'
        );

        $serializedHashes = $this->hashesBlock->getSerialized();
        $deployedAssets = $this->serializer->unserialize($serializedHashes);

        $mergedFileFound = false;
        foreach ($deployedAssets as $url => $hash) {
            if (strpos($url, '/_cache/merged/') !== false && strpos($url, basename($mergedAssetPath)) !== false) {
                $mergedFileFound = true;
                $this->assertStringStartsWith(
                    'sha',
                    $hash,
                    'SRI hash should start with algorithm (sha256, sha384, or sha512)'
                );
                break;
            }
        }

        $this->assertTrue(
            $mergedFileFound,
            sprintf(
                'Merged file should be in SRI repository after plugin runs (strategy: %s, file: %s)',
                $strategy,
                basename($mergedAssetPath)
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

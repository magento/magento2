<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Asset\Source;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests GenerateMergedAssetIntegrity plugin exception handling.
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateMergedAssetIntegrityTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var GenerateMergedAssetIntegrity
     */
    private GenerateMergedAssetIntegrity $plugin;

    /**
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $repository;

    /**
     * @var State
     */
    private State $appState;

    /**
     * @var string
     */
    private string $prevMode;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->appState = $objectManager->get(State::class);
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        $this->plugin = $objectManager->get(GenerateMergedAssetIntegrity::class);

        $repositoryPool = $objectManager->get(SubresourceIntegrityRepositoryPool::class);
        $this->repository = $repositoryPool->get(Area::AREA_FRONTEND);

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
     * Test that plugin handles readFile() exception gracefully.
     *
     * This tests the exception path where readFile() throws an exception
     * due to the file not existing or being unreadable.
     *
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @return void
     */
    public function testReadFileExceptionHandling(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Create a real asset that points to a non-existent file
        $context = $objectManager->create(
            FallbackContext::class,
            [
                'baseUrl' => 'http://localhost/pub/static/',
                'areaType' => Area::AREA_FRONTEND,
                'themePath' => 'Magento/blank',
                'localeCode' => 'en_US'
            ]
        );

        $resultAsset = $objectManager->create(
            File::class,
            [
                'source' => $objectManager->get(Source::class),
                'context' => $context,
                'filePath' => 'non/existent/file.js',
                'module' => '',
                'contentType' => 'js'
            ]
        );

        $subject = $objectManager->get(FileExists::class);

        // This should not throw exception - plugin catches and logs readFile() errors
        $result = $this->plugin->afterMerge($subject, '/path/to/result', [], $resultAsset);

        // Verify the result is returned unchanged and no exception was thrown
        $this->assertEquals('/path/to/result', $result);
    }

    /**
     * Test that plugin handles corrupted SRI hash storage file.
     *
     * Creates an invalid JSON file in the SRI hash storage location, then verifies
     * the plugin handles the error gracefully when trying to save new hashes.
     *
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 0
     * @return void
     * @throws FileSystemException
     */
    public function testHandlesInvalidJsonInStorageFile(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        // Create directory structure for merged files
        $mergedDir = '_cache/merged';
        $staticDir->create($mergedDir);

        // Create corrupted JSON in the SRI hash storage file for merged files
        // This simulates a scenario where the storage file was corrupted or partially written
        $storageFile = '_cache/merged/sri-hashes.json';
        $corruptedJson = '{"_cache\/merged\/test.min.js":"sha256-XxhRK2rESbdPzSetcTY0ZyN45+iuK9dzNnXR9gSuNMw="';
        $staticDir->writeFile($storageFile, $corruptedJson);

        // Create a valid merged JS file
        $testFile = $mergedDir . '/new-merged.js';
        $staticDir->writeFile($testFile, '/* New merged file */ console.log("test");');

        // Create an asset mirroring how the real merge pipeline creates merged assets.
        $assetRepository = $objectManager->get(AssetRepository::class);
        $resultAsset = $assetRepository->createArbitrary('new-merged.js', '_cache/merged');

        $subject = $objectManager->get(FileExists::class);

        // Call the plugin - should handle invalid JSON gracefully and log warning
        $result = $this->plugin->afterMerge($subject, '/path/to/result', [], $resultAsset);

        // Verify result is returned unchanged (plugin doesn't break the merge process)
        $this->assertEquals('/path/to/result', $result);

        // Verify the storage file was updated with the new hash despite the corrupted existing content
        $savedContent = $staticDir->readFile($storageFile);
        $this->assertJson($savedContent, 'Storage file should contain valid JSON after plugin handles corrupt data');
        $decoded = json_decode($savedContent, true);
        $this->assertArrayHasKey(
            '_cache/merged/new-merged.js',
            $decoded,
            'New hash should be saved despite corrupt existing storage'
        );
    }

    /**
     * Test that plugin successfully generates and saves hash for valid merged file.
     *
     * Creates a valid merged JS file, calls the plugin, and verifies:
     * - readFile() succeeds
     * - Hash is generated
     * - save() succeeds (hash is stored in repository)
     *
     * @magentoConfigFixture current_store dev/js/merge_files 1
     * @magentoConfigFixture current_store dev/static/sign 1
     * @return void
     * @throws FileSystemException
     */
    public function testSuccessfulHashGenerationAndSave(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        // Create a valid merged JS file
        $testDir = 'frontend/Magento/blank/en_US/_cache/merged';
        $testFile = $testDir . '/test-merged.js';
        $staticDir->create($testDir);
        $staticDir->writeFile($testFile, '/* Merged file */ console.log("test");');

        // Create a real asset that points to the merged file
        $context = $objectManager->create(
            FallbackContext::class,
            [
                'baseUrl' => 'http://localhost/pub/static/',
                'areaType' => Area::AREA_FRONTEND,
                'themePath' => 'Magento/blank',
                'localeCode' => 'en_US'
            ]
        );

        $resultAsset = $objectManager->create(
            File::class,
            [
                'source' => $objectManager->get(Source::class),
                'context' => $context,
                'filePath' => '_cache/merged/test-merged.js',
                'module' => '',
                'contentType' => 'js'
            ]
        );

        $subject = $objectManager->get(FileExists::class);

        // Call the plugin - should successfully generate and save hash
        $result = $this->plugin->afterMerge($subject, '/path/to/result', [], $resultAsset);

        // Verify result is returned unchanged
        $this->assertEquals('/path/to/result', $result);

        // Verify hash was generated and saved successfully
        $integrity = $this->repository->getByPath($testFile);
        $this->assertNotNull($integrity, 'Hash should be generated and saved for valid merged file');

        // Verify hash format
        $hash = $integrity->getHash();
        $this->assertStringStartsWith('sha', $hash, 'Hash should start with sha algorithm');
        $this->assertMatchesRegularExpression(
            '/^sha(256|384|512)-[A-Za-z0-9+\/=]+$/',
            $hash,
            'Hash should match SRI format'
        );
    }
}

<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Plugin\GenerateAssetIntegrity;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Asset\File;
use Magento\RequireJs\Model\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for GenerateAssetIntegrity plugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateAssetIntegrityTest extends TestCase
{
    /** Sample asset path with 4+ segments used across happy-path tests. */
    private const VALID_PATH    = 'frontend/Magento/luma/en_US/requirejs-min-resolver.js';

    /** Expected context (first 4 path segments) derived from VALID_PATH. */
    private const VALID_CONTEXT = 'frontend/Magento/luma/en_US';

    /** Minimal JS file content used as the stub return value for filesystem reads. */
    private const FILE_CONTENT  = 'require([]);' . "\n";

    /** Stub SRI hash string returned by the hash generator in shared test setup. */
    private const FILE_HASH     = 'sha256-abc123';

    /** @var MockObject&HashGenerator */
    private MockObject $hashGenerator;

    /** @var MockObject&SubresourceIntegrityFactory */
    private MockObject $integrityFactory;

    /** @var MockObject&Filesystem */
    private MockObject $filesystem;

    /** @var MockObject&LoggerInterface */
    private MockObject $logger;

    /** @var MockObject&ReadInterface */
    private MockObject $staticDir;

    /** @var MockObject&SubresourceIntegrity */
    private MockObject $integrity;

    /** @var MockObject&FileManager */
    private MockObject $fileManager;

    /** @var MockObject&File */
    private MockObject $assetFile;

    protected function setUp(): void
    {
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->staticDir = $this->createMock(ReadInterface::class);
        $this->integrity = $this->createMock(SubresourceIntegrity::class);
        $this->fileManager = $this->createMock(FileManager::class);
        $this->assetFile = $this->createMock(File::class);

        $this->filesystem->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->staticDir);
    }

    /**
     * Test afterCreateRequireJsConfigAsset reads raw bytes and saves via repository.
     */
    public function testAfterCreateRequireJsConfigAssetSavesHash(): void
    {
        [$plugin] = $this->buildPluginWithRepositoryExpectation();

        $result = $plugin->afterCreateRequireJsConfigAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
    }

    /**
     * Test afterCreateRequireJsMixinsAsset reads raw bytes and saves via repository.
     */
    public function testAfterCreateRequireJsMixinsAssetSavesHash(): void
    {
        [$plugin] = $this->buildPluginWithRepositoryExpectation();

        $result = $plugin->afterCreateRequireJsMixinsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
    }

    /**
     * Test afterCreateStaticJsAsset reads raw bytes and saves via repository.
     */
    public function testAfterCreateStaticJsAssetSavesHash(): void
    {
        [$plugin] = $this->buildPluginWithRepositoryExpectation();

        $result = $plugin->afterCreateStaticJsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
    }

    /**
     * Test afterCreateMinResolverAsset reads raw bytes and saves via repository.
     */
    public function testAfterCreateMinResolverAssetSavesHash(): void
    {
        [$plugin] = $this->buildPluginWithRepositoryExpectation();

        $result = $plugin->afterCreateMinResolverAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
    }

    /**
     * Test afterCreateStaticJsAsset skips when result is false (bundling disabled).
     */
    public function testAfterCreateStaticJsAssetSkipsWhenFalse(): void
    {
        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->never())->method('save');

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->willReturn($repository);

        $plugin = $this->buildPlugin($repositoryPool);

        $result = $plugin->afterCreateStaticJsAsset($this->fileManager, false);

        $this->assertFalse($result);
    }

    /**
     * Test that non-JS content types are skipped.
     */
    public function testSkipsNonJsContentType(): void
    {
        $this->assetFile->method('getContentType')->willReturn('css');

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->never())->method('save');

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->willReturn($repository);

        $plugin = $this->buildPlugin($repositoryPool);
        $plugin->afterCreateMinResolverAsset($this->fileManager, $this->assetFile);
    }

    /**
     * Test that a path with fewer than 4 segments logs a debug message and skips saving.
     */
    public function testSkipsAndLogsInvalidPath(): void
    {
        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getPath')->willReturn('bad/path.js');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('SRI: Skipping invalid path (< 4 segments)', ['path' => 'bad/path.js']);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->never())->method('save');

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->willReturn($repository);

        $plugin = new GenerateAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->createMock(SubresourceIntegrityCollector::class),
            $repositoryPool,
            $this->filesystem,
            $logger
        );

        $plugin->afterCreateMinResolverAsset($this->fileManager, $this->assetFile);
    }

    /**
     * Test that a filesystem read failure is caught silently and save is not called.
     */
    public function testSkipsWhenFilesystemReadFails(): void
    {
        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getPath')->willReturn(self::VALID_PATH);

        $staticDir = $this->createMock(ReadInterface::class);
        $staticDir->method('readFile')->willThrowException(new \Exception('File not found'));

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($staticDir);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->never())->method('save');

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->willReturn($repository);

        $plugin = new GenerateAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->createMock(SubresourceIntegrityCollector::class),
            $repositoryPool,
            $filesystem,
            $this->logger
        );

        $plugin->afterCreateMinResolverAsset($this->fileManager, $this->assetFile);
    }

    /**
     * Test that the hash is computed from raw file bytes, not from getContent().
     *
     * This is the core regression guard: File::getContent() calls trim(),
     * which strips leading/trailing whitespace and produces a different hash than
     * the browser computes from the raw bytes on disk.
     */
    public function testHashComputedFromRawBytesNotGetContent(): void
    {
        $rawContent = '    require([]);' . "\n"; // leading spaces + trailing newline

        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getPath')->willReturn(self::VALID_PATH);

        // getContent() must never be called — the trim() bug lives there
        $assetFile = $this->createMock(File::class);
        $assetFile->method('getContentType')->willReturn('js');
        $assetFile->method('getPath')->willReturn(self::VALID_PATH);
        $assetFile->expects($this->never())->method('getContent');

        $staticDir = $this->createMock(ReadInterface::class);
        $staticDir->method('readFile')->with(self::VALID_PATH)->willReturn($rawContent);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($staticDir);

        $hashGenerator = $this->createMock(HashGenerator::class);
        $hashGenerator->expects($this->once())
            ->method('generate')
            ->with($rawContent) // must receive raw bytes, not trimmed
            ->willReturn(self::FILE_HASH);

        $integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
        $integrityFactory->method('create')->willReturn($this->integrity);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->once())->method('save')->with($this->integrity);

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->with(self::VALID_CONTEXT)->willReturn($repository);

        $plugin = new GenerateAssetIntegrity(
            $hashGenerator,
            $integrityFactory,
            $this->createMock(SubresourceIntegrityCollector::class),
            $repositoryPool,
            $filesystem,
            $this->logger
        );

        $plugin->afterCreateMinResolverAsset($this->fileManager, $assetFile);
    }

    /**
     * Test that context is correctly derived as the first 4 path segments.
     *
     * e.g. frontend/Magento/luma/en_US/mage/requirejs/mixins.js
     *   => context: frontend/Magento/luma/en_US
     */
    public function testContextDerivedFromFirst4PathSegments(): void
    {
        $deepPath = 'frontend/Magento/luma/en_US/mage/requirejs/mixins.js';

        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getPath')->willReturn($deepPath);
        $this->staticDir->method('readFile')->willReturn(self::FILE_CONTENT);
        $this->hashGenerator->method('generate')->willReturn(self::FILE_HASH);
        $this->integrityFactory->method('create')->willReturn($this->integrity);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->once())->method('save');

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->expects($this->once())
            ->method('get')
            ->with(self::VALID_CONTEXT) // area/vendor/theme/locale only — no filename
            ->willReturn($repository);

        $plugin = $this->buildPlugin($repositoryPool);
        $plugin->afterCreateRequireJsMixinsAsset($this->fileManager, $this->assetFile);
    }

    /**
     * Build a plugin instance and a repository mock that expects save() once,
     * with all shared stubs pre-configured for a successful hash generation.
     *
     * @return array{0: GenerateAssetIntegrity, 1: SubresourceIntegrityRepository}
     */
    private function buildPluginWithRepositoryExpectation(): array
    {
        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getPath')->willReturn(self::VALID_PATH);
        $this->staticDir->method('readFile')->with(self::VALID_PATH)->willReturn(self::FILE_CONTENT);
        $this->hashGenerator->method('generate')->willReturn(self::FILE_HASH);
        $this->integrityFactory->method('create')->willReturn($this->integrity);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->once())->method('save')->with($this->integrity);

        $repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $repositoryPool->method('get')->with(self::VALID_CONTEXT)->willReturn($repository);

        return [$this->buildPlugin($repositoryPool), $repository];
    }

    /**
     * Build a plugin with a given repository pool, using the shared stubs for other deps.
     */
    private function buildPlugin(SubresourceIntegrityRepositoryPool $repositoryPool): GenerateAssetIntegrity
    {
        return new GenerateAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->createMock(SubresourceIntegrityCollector::class),
            $repositoryPool,
            $this->filesystem,
            $this->logger
        );
    }
}

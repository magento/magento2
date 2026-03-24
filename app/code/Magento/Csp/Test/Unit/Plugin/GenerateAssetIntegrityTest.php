<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Plugin\GenerateAssetIntegrity;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Framework\View\Asset\File;
use Magento\RequireJs\Model\FileManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test for GenerateAssetIntegrity plugin.
 */
class GenerateAssetIntegrityTest extends TestCase
{
    /**
     * @var GenerateAssetIntegrity
     */
    private $plugin;

    /**
     * @var HashGenerator|MockObject
     */
    private $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory|MockObject
     */
    private $integrityFactory;

    /**
     * @var SubresourceIntegrityCollector|MockObject
     */
    private $integrityCollector;

    /**
     * @var FileManager|MockObject
     */
    private $fileManager;

    /**
     * @var File|MockObject
     */
    private $assetFile;

    /**
     * @var SubresourceIntegrity|MockObject
     */
    private $integrity;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
        $this->integrityCollector = $this->createMock(SubresourceIntegrityCollector::class);
        $this->fileManager = $this->createMock(FileManager::class);
        $this->assetFile = $this->createMock(File::class);
        $this->integrity = $this->createMock(SubresourceIntegrity::class);

        $this->plugin = new GenerateAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->integrityCollector
        );
    }

    /**
     * Test afterCreateRequireJsConfigAsset with JS content type.
     */
    public function testAfterCreateRequireJsConfigAssetWithJsContent(): void
    {
        $this->mockJsAssetFile();
        $this->mockIntegrityCreation('test-hash', 'test/path.js');

        $result = $this->plugin->afterCreateRequireJsConfigAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);

        $this->integrityCollector->expects($this->any())->method('collect');
    }

    /**
     * Test afterCreateRequireJsMixinsAsset with JS content type.
     */
    public function testAfterCreateRequireJsMixinsAssetWithJsContent(): void
    {
        $this->mockJsAssetFile();
        $this->mockIntegrityCreation('test-hash', 'test/path.js');

        $result = $this->plugin->afterCreateRequireJsMixinsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
        $this->integrityCollector->expects($this->any())->method('collect');
    }

    /**
     * Test afterCreateRequireJsMixinsAsset with null content.
     */
    public function testAfterCreateRequireJsMixinsAssetWithNullContent(): void
    {
        $this->mockJsAssetFileWithNullContent();

        $result = $this->plugin->afterCreateRequireJsMixinsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
        $this->integrityCollector->expects($this->any())->method('collect');
    }

    /**
     * Test afterCreateStaticJsAsset with JS content type.
     */
    public function testAfterCreateStaticJsAssetWithJsContent(): void
    {
        $this->mockJsAssetFile();
        $this->mockIntegrityCreation('test-hash', 'test/path.js');

        $result = $this->plugin->afterCreateStaticJsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);

        $this->integrityCollector->expects($this->any())->method('collect');
    }

    /**
     * Test afterCreateStaticJsAsset with null content.
     */
    public function testAfterCreateStaticJsAssetWithNullContent(): void
    {
        $this->mockJsAssetFileWithNullContent();

        $result = $this->plugin->afterCreateStaticJsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
        $this->integrityCollector->expects($this->never())->method('collect');
    }

    /**
     * Test that the plugin correctly handles null content.
     */
    public function testPluginLogicWithNullContent(): void
    {
        $this->mockJsAssetFileWithNullContent();

        $result = $this->plugin->afterCreateRequireJsMixinsAsset($this->fileManager, $this->assetFile);

        $this->assertSame($this->assetFile, $result);
        $this->integrityCollector->expects($this->never())->method('collect');
    }

    /**
     * Mock JS asset file with valid content.
     */
    private function mockJsAssetFile(): void
    {
        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getContent')->willReturn('console.log("test");');
        $this->assetFile->method('getPath')->willReturn('test/path.js');
    }

    /**
     * Mock JS asset file with null content.
     */
    private function mockJsAssetFileWithNullContent(): void
    {
        $this->assetFile->method('getContentType')->willReturn('js');
        $this->assetFile->method('getContent')->willReturn(null);
        $this->assetFile->method('getPath')->willReturn('test/path.js');
    }

    /**
     * Mock integrity creation.
     */
    private function mockIntegrityCreation(string $hash, string $path): void
    {
        $this->hashGenerator->method('generate')->willReturn($hash);

        $this->integrityFactory->method('create')
            ->willReturn($this->integrity);

        $this->integrity->method('getHash')->willReturn($hash);
        $this->integrity->method('getPath')->willReturn($path);
    }
}

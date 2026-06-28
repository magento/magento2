<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Plugin\GenerateBundleAssetIntegrity;
use Magento\Deploy\Service\Bundle;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GenerateBundleAssetIntegrityTest extends TestCase
{
    /**
     * @var HashGenerator
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var SubresourceIntegrityCollector
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var File
     */
    private File $fileIo;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * Initialize Dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
        $this->integrityCollector = $this->createMock(SubresourceIntegrityCollector::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileIo = $this->createMock(File::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->repositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
    }

    /**
     * @return void
     */
    public function testAfterDeploy(): void
    {
        $subject = $this->createMock(Bundle::class);
        $result = null;
        $area = 'frontend';
        $theme = 'Magento/blank';
        $locale = 'en_US';
        $file = '/path/to/file.js';
        $hash = 'asdfghjkl';
        $fileContent = 'content';

        $pubStaticDir = $this->createMock(ReadInterface::class);
        $pubStaticDir->expects($this->once())->method('search')->with(
            $area ."/" . $theme . "/" . $locale . "/" . Bundle::BUNDLE_JS_DIR . "/*.js"
        )->willReturn([$file]);
        $pubStaticDir->expects($this->once())->method('readFile')->willReturn($fileContent);
        $this->filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($pubStaticDir);

        $integrity = $this->createMock(SubresourceIntegrity::class);
        $this->hashGenerator->expects($this->once())
            ->method('generate')
            ->with($fileContent)
            ->willReturn($hash);
        $this->fileIo->expects($this->once())
            ->method('getPathInfo')
            ->with($file)
            ->willReturn(['basename' => 'file.js']);
        $this->integrityFactory->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'hash' => $hash,
                    'path' => $area . '/' . $theme . '/' . $locale . '/' . Bundle::BUNDLE_JS_DIR . '/file.js'
                ]
            ])
            ->willReturn($integrity);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->once())->method('save')->with($integrity);
        $this->repositoryPool->expects($this->once())->method('get')
            ->with($area . '/' . $theme . '/' . $locale)
            ->willReturn($repository);

        $plugin = new GenerateBundleAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->integrityCollector,
            $this->filesystem,
            $this->fileIo,
            $this->logger,
            $this->repositoryPool
        );
        $plugin->afterDeploy($subject, $result, $area, $theme, $locale);
    }
}

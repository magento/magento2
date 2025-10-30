<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityCollector;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Plugin\GenerateBundleAssetIntegrity;
use Magento\Deploy\Service\Bundle;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateBundleAssetIntegrityTest extends TestCase
{
    /**
     * @var HashGenerator|MockObject
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory|MockObject
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @var SubresourceIntegrityCollector|MockObject
     */
    private SubresourceIntegrityCollector $integrityCollector;

    /**
     * @var Filesystem|MockObject
     */
    private Filesystem $filesystem;

    /**
     * @var File|MockObject
     */
    private File $fileIo;

    /**
     * Initialize Dependencies
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
        $this->integrityCollector = $this->createMock(SubresourceIntegrityCollector::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileIo = $this->createMock(File::class);
    }

    /**
     * @return void
     * @throws Exception
     * @throws FileSystemException
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
        $this->integrityCollector->expects($this->once())->method('collect')->with($integrity);

        $plugin = new GenerateBundleAssetIntegrity(
            $this->hashGenerator,
            $this->integrityFactory,
            $this->integrityCollector,
            $this->filesystem,
            $this->fileIo
        );
        $plugin->afterDeploy($subject, $result, $area, $theme, $locale);
    }
}

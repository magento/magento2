<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrity\HashGenerator;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Magento\Csp\Plugin\GenerateMergedAssetIntegrity;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateMergedAssetIntegrityTest extends TestCase
{
    /**
     * @var SubresourceIntegrityRepositoryPool|MockObject
     */
    private SubresourceIntegrityRepositoryPool $sourceIntegrityRepository;

    /**
     * @var HashGenerator|MockObject
     */
    private HashGenerator $hashGenerator;

    /**
     * @var SubresourceIntegrityFactory|MockObject
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->sourceIntegrityRepository = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->hashGenerator = $this->createMock(HashGenerator::class);
        $this->integrityFactory = $this->createMock(SubresourceIntegrityFactory::class);
    }

    /**
     * @return void
     * @throws Exception|FileSystemException
     */
    public function testAfterMerge(): void
    {
        $subject = $this->createMock(FileExists::class);
        $result = null;
        $assetsToMerge = [];
        $fileExtension = 'js';
        $filePath = 'path/to/file.js';
        $hash = '1234567890abcdef';
        $fileContent = 'some content';
        $resultAsset = $this->createMock(File::class);
        $resultAsset->expects($this->once())->method('getContentType')->willReturn($fileExtension);
        $resultAsset->expects($this->once())->method('getContent')->willReturn($fileContent);
        $resultAsset->expects($this->once())
            ->method('getPath')
            ->willReturn($filePath);
        $this->hashGenerator->expects($this->once())->method('generate')->with($fileContent)->willReturn($hash);
        $integrity = $this->createMock(SubresourceIntegrity::class);
        $this->integrityFactory->expects($this->once())
            ->method('create')->with([
                'data' => [
                    'hash' => $hash,
                    'path' => $filePath
                ]
            ])->willReturn($integrity);
        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $this->sourceIntegrityRepository->expects($this->once())->method('get')
            ->with(Area::AREA_FRONTEND)
            ->willReturn($repository);
        $repository->expects($this->once())->method('save')->with($integrity);

        $plugin = new GenerateMergedAssetIntegrity(
            $this->sourceIntegrityRepository,
            $this->hashGenerator,
            $this->integrityFactory,
            $this->createMock(Filesystem::class)
        );
        $plugin->afterMerge($subject, $result, $assetsToMerge, $resultAsset);
    }
}

<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\MergeStrategy\Direct;
use Magento\Framework\View\Url\CssResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\View\Asset\MergeStrategy\Direct.
 */
class DirectTest extends TestCase
{
    /**
     * @var Direct
     */
    private $model;

    /**
     * @var Random|MockObject
     */
    private $mathRandomMock;

    /**
     * @var MockObject|CssResolver
     */
    private $cssUrlResolverMock;

    /**
     * @var MockObject|WriteInterface
     */
    private $staticDirMock;

    /**
     * @var MockObject|LocalInterface
     */
    private $resultAssetMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->cssUrlResolverMock = $this->createMock(CssResolver::class);
        $this->staticDirMock = $this->getMockForAbstractClass(WriteInterface::class);
        $tmpDir = $this->getMockForAbstractClass(WriteInterface::class);

        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturnMap([
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirMock],
                [DirectoryList::TMP, DriverPool::FILE, $tmpDir],
            ]);

        $this->resultAssetMock = $this->createMock(File::class);
        $this->mathRandomMock = $this->createMock(Random::class);
        $this->model = new Direct($filesystemMock, $this->cssUrlResolverMock, $this->mathRandomMock);
    }

    public function testMergeNoAssets()
    {
        $uniqId = '_b3bf82fa6e140594420fa90982a8e877';
        $this->resultAssetMock->expects($this->once())
            ->method('getPath')
            ->willReturn('foo/result');
        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);
        $this->staticDirMock->expects($this->once())
            ->method('writeFile')
            ->with('foo/result' . $uniqId, '');
        $this->staticDirMock->expects($this->once())
            ->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDirMock);

        $this->model->merge([], $this->resultAssetMock);
    }

    public function testMergeGeneric()
    {
        $uniqId = '_be50ccf992fd81818c1a2645d1a29e92';
        $assets = $this->prepareAssetsToMerge([' one', 'two']); // note leading space intentionally

        $this->resultAssetMock->expects($this->once())
            ->method('getPath')
            ->willReturn('foo/result');

        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);

        $this->staticDirMock->expects($this->once())
            ->method('writeFile')
            ->with('foo/result' . $uniqId, 'onetwo');
        $this->staticDirMock->expects($this->once())
            ->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDirMock);

        $this->model->merge($assets, $this->resultAssetMock);
    }

    public function testMergeCss()
    {
        $uniqId = '_f929c374767e00712449660ea673f2f5';
        $this->resultAssetMock->expects($this->exactly(3))
            ->method('getPath')
            ->willReturn('foo/result');
        $this->resultAssetMock->expects($this->atLeastOnce())
            ->method('getContentType')
            ->willReturn('css');
        $assets = $this->prepareAssetsToMerge(['one', 'two']);
        $this->cssUrlResolverMock->expects($this->exactly(2))
            ->method('relocateRelativeUrls')
            ->will($this->onConsecutiveCalls('1', '2'));
        $this->cssUrlResolverMock->expects($this->once())
            ->method('aggregateImportDirectives')
            ->with('12')
            ->willReturn('1020');
        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);
        $this->staticDirMock->expects($this->once())
            ->method('writeFile')
            ->with('foo/result' . $uniqId, '1020');
        $this->staticDirMock->expects($this->once())
            ->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDirMock);

        $this->model->merge($assets, $this->resultAssetMock);
    }

    /**
     * Prepare a few assets for merging with specified content
     *
     * @param array $data
     * @return array
     */
    private function prepareAssetsToMerge(array $data): array
    {
        $result = [];
        foreach ($data as $content) {
            $asset = $this->getMockForAbstractClass(LocalInterface::class);
            $asset->expects($this->once())->method('getContent')->willReturn($content);
            $result[] = $asset;
        }
        return $result;
    }
}

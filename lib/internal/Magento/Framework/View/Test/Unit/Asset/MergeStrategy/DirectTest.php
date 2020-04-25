<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var Random|MockObject
     */
    protected $mathRandomMock;
    /**
     * @var Direct
     */
    protected $object;

    /**
     * @var MockObject|CssResolver
     */
    protected $cssUrlResolver;

    /**
     * @var MockObject|WriteInterface
     */
    protected $staticDir;

    /**
     * @var MockObject|WriteInterface
     */
    protected $tmpDir;

    /**
     * @var MockObject|LocalInterface
     */
    protected $resultAsset;

    protected function setUp(): void
    {
        $this->cssUrlResolver = $this->createMock(CssResolver::class);
        $filesystem = $this->createMock(Filesystem::class);
        $this->staticDir = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $this->tmpDir = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturnMap([
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDir],
                [DirectoryList::TMP, DriverPool::FILE, $this->tmpDir],
            ]);
        $this->resultAsset = $this->createMock(File::class);
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->object = new Direct($filesystem, $this->cssUrlResolver, $this->mathRandomMock);
    }

    public function testMergeNoAssets()
    {
        $uniqId = '_b3bf82fa6e140594420fa90982a8e877';
        $this->resultAsset->expects($this->once())->method('getPath')->willReturn('foo/result');
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result' . $uniqId, '');
        $this->tmpDir->expects($this->once())->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDir);
        $this->object->merge([], $this->resultAsset);
    }

    public function testMergeGeneric()
    {
        $uniqId = '_be50ccf992fd81818c1a2645d1a29e92';
        $this->resultAsset->expects($this->once())->method('getPath')->willReturn('foo/result');
        $assets = $this->prepareAssetsToMerge([' one', 'two']); // note leading space intentionally
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result' . $uniqId, 'onetwo');
        $this->tmpDir->expects($this->once())->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDir);
        $this->object->merge($assets, $this->resultAsset);
    }

    public function testMergeCss()
    {
        $uniqId = '_f929c374767e00712449660ea673f2f5';
        $this->resultAsset->expects($this->exactly(3))
            ->method('getPath')
            ->willReturn('foo/result');
        $this->resultAsset->expects($this->any())->method('getContentType')->willReturn('css');
        $assets = $this->prepareAssetsToMerge(['one', 'two']);
        $this->cssUrlResolver->expects($this->exactly(2))
            ->method('relocateRelativeUrls')
            ->will($this->onConsecutiveCalls('1', '2'));
        $this->cssUrlResolver->expects($this->once())
            ->method('aggregateImportDirectives')
            ->with('12')
            ->willReturn('1020');
        $this->mathRandomMock->expects($this->once())
            ->method('getUniqueHash')
            ->willReturn($uniqId);
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result' . $uniqId, '1020');
        $this->tmpDir->expects($this->once())->method('renameFile')
            ->with('foo/result' . $uniqId, 'foo/result', $this->staticDir);
        $this->object->merge($assets, $this->resultAsset);
    }

    /**
     * Prepare a few assets for merging with specified content
     *
     * @param array $data
     * @return array
     */
    private function prepareAssetsToMerge(array $data)
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use Magento\Framework\View\Asset\MergeStrategy\Direct;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Direct merge strategy test.
 */
class DirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\MergeStrategy\Direct
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Url\CssResolver
     */
    protected $cssUrlResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WriteInterface
     */
    protected $staticDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WriteInterface
     */
    protected $tmpDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\LocalInterface
     */
    protected $resultAsset;

    protected function setUp()
    {
        $this->cssUrlResolver = $this->getMock(\Magento\Framework\View\Url\CssResolver::class);
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->staticDir = $this->getMockBuilder(WriteInterface::class)->getMockForAbstractClass();
        $this->tmpDir = $this->getMockBuilder(WriteInterface::class)->getMockForAbstractClass();
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturnMap([
                [DirectoryList::STATIC_VIEW, \Magento\Framework\Filesystem\DriverPool::FILE, $this->staticDir],
                [DirectoryList::TMP, \Magento\Framework\Filesystem\DriverPool::FILE, $this->tmpDir],
            ]);
        $this->resultAsset = $this->getMock(\Magento\Framework\View\Asset\File::class, [], [], '', false);
        $this->object = new Direct($filesystem, $this->cssUrlResolver);
    }

    public function testMergeNoAssets()
    {
        $this->resultAsset->expects($this->once())->method('getPath')->will($this->returnValue('foo/result'));
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result', '');
        $this->tmpDir->expects($this->once())->method('renameFile')->with('foo/result', 'foo/result', $this->staticDir);
        $this->object->merge([], $this->resultAsset);
    }

    public function testMergeGeneric()
    {
        $this->resultAsset->expects($this->once())->method('getPath')->will($this->returnValue('foo/result'));
        $assets = $this->prepareAssetsToMerge([' one', 'two']); // note leading space intentionally
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result', 'onetwo');
        $this->tmpDir->expects($this->once())->method('renameFile')->with('foo/result', 'foo/result', $this->staticDir);
        $this->object->merge($assets, $this->resultAsset);
    }

    public function testMergeCss()
    {
        $this->resultAsset->expects($this->exactly(3))
            ->method('getPath')
            ->will($this->returnValue('foo/result'));
        $this->resultAsset->expects($this->any())->method('getContentType')->will($this->returnValue('css'));
        $assets = $this->prepareAssetsToMerge(['one', 'two']);
        $this->cssUrlResolver->expects($this->exactly(2))
            ->method('relocateRelativeUrls')
            ->will($this->onConsecutiveCalls('1', '2'));
        $this->cssUrlResolver->expects($this->once())
            ->method('aggregateImportDirectives')
            ->with('12')
            ->will($this->returnValue('1020'));
        $this->staticDir->expects($this->never())->method('writeFile');
        $this->tmpDir->expects($this->once())->method('writeFile')->with('foo/result', '1020');
        $this->tmpDir->expects($this->once())->method('renameFile')->with('foo/result', 'foo/result', $this->staticDir);
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
            $asset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
            $asset->expects($this->once())->method('getContent')->will($this->returnValue($content));
            $result[] = $asset;
        }
        return $result;
    }
}

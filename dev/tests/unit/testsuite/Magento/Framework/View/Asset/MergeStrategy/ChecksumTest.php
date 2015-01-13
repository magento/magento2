<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;

class ChecksumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $sourceDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $targetDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File
     */
    private $resultAsset;

    /**
     * @var \Magento\Framework\View\Asset\MergeStrategy\Checksum
     */
    private $checksum;

    protected function setUp()
    {
        $this->mergerMock = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\MergeStrategyInterface');
        $this->sourceDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->targetDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($this->sourceDir));
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->targetDir));
        $this->checksum = new Checksum($this->mergerMock, $filesystem);
        $this->resultAsset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
    }

    public function testMergeNoAssets()
    {
        $this->mergerMock->expects($this->never())->method('merge');
        $this->checksum->merge([], $this->resultAsset);
    }

    public function testMergeNoDatFile()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(false));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->once())->method('merge')->with($assets, $this->resultAsset);
        $this->targetDir->expects($this->once())->method('writeFile')->with('merged/result.txt.dat', '11');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    public function testMergeMtimeChanged()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(true));
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue('10'));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->once())->method('merge')->with($assets, $this->resultAsset);
        $this->targetDir->expects($this->once())->method('writeFile')->with('merged/result.txt.dat', '11');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    public function testMergeMtimeUnchanged()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(true));
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue('11'));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->never())->method('merge');
        $this->targetDir->expects($this->never())->method('writeFile');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    /**
     * Create mocks of assets to merge, as well as a few related necessary mocks
     *
     * @return array
     */
    private function getAssetsToMerge()
    {
        $one = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $one->expects($this->once())->method('getSourceFile')->will($this->returnValue('/dir/file/one.txt'));
        $two = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $two->expects($this->once())->method('getSourceFile')->will($this->returnValue('/dir/file/two.txt'));
        $this->sourceDir->expects($this->exactly(2))
            ->method('getRelativePath')
            ->will($this->onConsecutiveCalls('file/one.txt', 'file/two.txt'));
        $this->sourceDir->expects($this->exactly(2))->method('stat')->will($this->returnValue(['mtime' => '1']));
        $this->resultAsset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('merged/result.txt'));
        return [$one, $two];
    }
}

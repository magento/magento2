<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use \Magento\Framework\View\Asset\MergeStrategy\Checksum;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Source;

class ChecksumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Asset\MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $sourceDir;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $targetDir;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Asset\File
     */
    private $resultAsset;

    /**
     * @var Source|\PHPUnit\Framework\MockObject\MockObject
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\View\Asset\MergeStrategy\Checksum
     */
    private $checksum;

    protected function setUp(): void
    {
        $this->mergerMock = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\MergeStrategyInterface::class);
        $this->sourceDir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->targetDir = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->sourceDir);
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->targetDir);
        $this->checksum = new Checksum($this->mergerMock, $filesystem);
        $this->assetSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new \ReflectionClass(Checksum::class);
        $reflectionProperty = $reflection->getProperty('assetSource');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->checksum, $this->assetSource);

        $this->resultAsset = $this->createMock(\Magento\Framework\View\Asset\File::class);
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
            ->willReturn(false);
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
            ->willReturn(true);
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->willReturn('10');
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
            ->willReturn(true);
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->willReturn('11');
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
        $one = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $two = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $one->expects($this->never())
            ->method('getSourceFile');
        $two->expects($this->never())
            ->method('getSourceFile');

        $this->assetSource->expects($this->exactly(2))
            ->method('findSource')
            ->withConsecutive([$one], [$two])
            ->willReturnOnConsecutiveCalls('/dir/file/one.txt', '/dir/file/two.txt');

        $this->sourceDir->expects($this->exactly(2))
            ->method('getRelativePath')
            ->will($this->onConsecutiveCalls('file/one.txt', 'file/two.txt'));
        $this->sourceDir->expects($this->exactly(2))->method('stat')->willReturn(['mtime' => '1']);
        $this->resultAsset->expects($this->once())
            ->method('getPath')
            ->willReturn('merged/result.txt');
        return [$one, $two];
    }
}

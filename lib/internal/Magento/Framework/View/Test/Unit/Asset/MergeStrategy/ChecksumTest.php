<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\MergeStrategy\Checksum;
use Magento\Framework\View\Asset\MergeStrategyInterface;
use Magento\Framework\View\Asset\Source;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChecksumTest extends TestCase
{
    /**
     * @var MockObject|MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var MockObject|ReadInterface
     */
    private $sourceDir;

    /**
     * @var MockObject|WriteInterface
     */
    private $targetDir;

    /**
     * @var MockObject|File
     */
    private $resultAsset;

    /**
     * @var Source|MockObject
     */
    private $assetSource;

    /**
     * @var Checksum
     */
    private $checksum;

    protected function setUp(): void
    {
        $this->mergerMock = $this->getMockForAbstractClass(MergeStrategyInterface::class);
        $this->sourceDir = $this->getMockForAbstractClass(ReadInterface::class);
        $this->targetDir = $this->getMockForAbstractClass(
            WriteInterface::class
        );
        $filesystem = $this->createMock(Filesystem::class);
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

        $this->resultAsset = $this->createMock(File::class);
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
        $one = $this->createMock(File::class);
        $two = $this->createMock(File::class);
        $one->expects($this->never())
            ->method('getSourceFile');
        $two->expects($this->never())
            ->method('getSourceFile');

        $this->assetSource->expects($this->exactly(2))
            ->method('findSource')
            ->willReturnCallback(
                function ($arg) use ($one, $two) {
                    if ($arg == $one) {
                        return '/dir/file/one.txt';
                    } elseif ($arg == $two) {
                        return '/dir/file/two.txt';
                    }
                }
            );

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

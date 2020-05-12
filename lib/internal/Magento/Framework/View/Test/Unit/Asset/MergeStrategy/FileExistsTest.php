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
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Magento\Framework\View\Asset\MergeStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class FileExistsTest extends TestCase
{
    /**
     * @var MockObject|MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var MockObject|WriteInterface
     */
    private $dirMock;

    /**
     * @var MockObject|File
     */
    private $resultAsset;

    /**
     * @var FileExists
     */
    private $fileExists;

    protected function setUp(): void
    {
        $this->mergerMock = $this->getMockForAbstractClass(MergeStrategyInterface::class);
        $this->dirMock = $this->getMockForAbstractClass(ReadInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->dirMock);
        $this->fileExists = new FileExists($this->mergerMock, $filesystem);
        $this->resultAsset = $this->createMock(File::class);
        $this->resultAsset->expects($this->once())->method('getPath')->willReturn('foo/file');
    }

    public function testMergeExists()
    {
        $this->dirMock->expects($this->once())->method('isExist')->with('foo/file')->willReturn(true);
        $this->mergerMock->expects($this->never())->method('merge');
        $this->fileExists->merge([], $this->resultAsset);
    }

    public function testMergeNotExists()
    {
        $this->dirMock->expects($this->once())->method('isExist')->with('foo/file')->willReturn(false);
        $this->mergerMock->expects($this->once())->method('merge')->with([], $this->resultAsset);
        $this->fileExists->merge([], $this->resultAsset);
    }
}

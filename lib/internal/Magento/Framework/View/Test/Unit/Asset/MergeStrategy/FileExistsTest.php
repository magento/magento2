<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use \Magento\Framework\View\Asset\MergeStrategy\FileExists;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileExistsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $dirMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File
     */
    private $resultAsset;

    /**
     * @var \Magento\Framework\View\Asset\MergeStrategy\FileExists
     */
    private $fileExists;

    protected function setUp()
    {
        $this->mergerMock = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\MergeStrategyInterface::class);
        $this->dirMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->dirMock));
        $this->fileExists = new FileExists($this->mergerMock, $filesystem);
        $this->resultAsset = $this->createMock(\Magento\Framework\View\Asset\File::class);
        $this->resultAsset->expects($this->once())->method('getPath')->will($this->returnValue('foo/file'));
    }

    public function testMergeExists()
    {
        $this->dirMock->expects($this->once())->method('isExist')->with('foo/file')->will($this->returnValue(true));
        $this->mergerMock->expects($this->never())->method('merge');
        $this->fileExists->merge([], $this->resultAsset);
    }

    public function testMergeNotExists()
    {
        $this->dirMock->expects($this->once())->method('isExist')->with('foo/file')->will($this->returnValue(false));
        $this->mergerMock->expects($this->once())->method('merge')->with([], $this->resultAsset);
        $this->fileExists->merge([], $this->resultAsset);
    }
}

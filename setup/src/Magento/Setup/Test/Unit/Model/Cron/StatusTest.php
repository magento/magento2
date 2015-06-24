<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Setup\Model\Cron\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Status
     */
    private $status;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $baseReaderWriter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $varReaderWriter;

    public function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->baseReaderWriter = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface',
            [],
            '',
            false
        );
        $this->varReaderWriter = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface',
            [],
            '',
            false
        );
        $valueMap = [
            [DirectoryList::ROOT, DriverPool::FILE, $this->baseReaderWriter],
            [DirectoryList::VAR_DIR, DriverPool::FILE, $this->varReaderWriter],
        ];
        $this->filesystem->expects($this->exactly(2))
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap($valueMap));
        $this->status = new Status($this->filesystem);
    }

    public function testGetStatusFilePath()
    {
        $this->baseReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('update/var/.update_status.txt')
            ->willReturn('DIR/update/var/.update_status.txt');
        $this->assertEquals('DIR/update/var/.update_status.txt', $this->status->getStatusFilePath());
    }

    public function testGetLogFilePath()
    {
        $this->baseReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('update/var/update_status.log')
            ->willReturn('DIR/update/var/update_status.log');
        $this->assertEquals('DIR/update/var/update_status.log', $this->status->getLogFilePath());
    }

    public function testAdd()
    {
        $this->baseReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->baseReaderWriter->expects($this->at(1))->method('writeFile');
        $this->baseReaderWriter->expects($this->at(2))->method('isExist')->willReturn(true);
        $this->baseReaderWriter->expects($this->at(3))->method('readFile')->willReturn('test0');
        $this->baseReaderWriter->expects($this->at(4))->method('writeFile');
        $this->status->add('test1');
    }

    public function testToggleUpdateInProgressTrue()
    {
        $this->varReaderWriter->expects($this->once())->method('touch');
        $this->status->toggleUpdateInProgress(true);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ".update_in_progress.flag" cannot be created
     */
    public function testToggleUpdateInProgressTrueException()
    {
        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new \Magento\Framework\Phrase('Exception')));
        $this->status->toggleUpdateInProgress(true);
    }

    public function testToggleUpdateInProgressFalseFlagExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(true);
        $this->varReaderWriter->expects($this->at(1))->method('delete');
        $this->status->toggleUpdateInProgress(false);
    }
    public function testToggleUpdateInProgressFalseFlagNotExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->never())->method('delete');
        $this->status->toggleUpdateInProgress(false);
    }

    public function testToggleUpdateErrorTrue()
    {
        $this->varReaderWriter->expects($this->once())->method('touch');
        $this->status->toggleUpdateError(true);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage ".update_error.flag" cannot be created
     */
    public function testToggleUpdateErrorTrueException()
    {
        $this->varReaderWriter->expects($this->once())
            ->method('touch')
            ->willThrowException(new FileSystemException(new \Magento\Framework\Phrase('Exception')));
        $this->status->toggleUpdateError(true);
    }

    public function testToggleUpdateErrorFalseFlagExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(true);
        $this->varReaderWriter->expects($this->at(1))->method('delete');
        $this->status->toggleUpdateError(false);
    }
    public function testToggleUpdateErrorFalseFlagNotExist()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->never())->method('delete');
        $this->status->toggleUpdateError(false);
    }

    public function testIsUpdateError()
    {
        $this->varReaderWriter->expects($this->once())->method('isExist')->willReturn(true);
        $this->assertTrue($this->status->isUpdateError());
    }
}

namespace Magento\Setup\Model\Cron;

function chmod()
{
    return;
}

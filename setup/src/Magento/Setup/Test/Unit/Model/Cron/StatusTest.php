<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Exception\FileSystemException;
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
    private $varReaderWriter;

    public function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->varReaderWriter = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface',
            [],
            '',
            false
        );
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->varReaderWriter));
        $this->status = new Status($this->filesystem);
    }

    public function testGetStatusFilePath()
    {
        $this->varReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('.update_status.txt')
            ->willReturn('DIR/var/.update_status.txt');
        $this->assertEquals('DIR/var/.update_status.txt', $this->status->getStatusFilePath());
    }

    public function testGetLogFilePath()
    {
        $this->varReaderWriter->expects($this->any())
            ->method('getAbsolutePath')
            ->with('update_status.log')
            ->willReturn('DIR/var/update_status.log');
        $this->assertEquals('DIR/var/update_status.log', $this->status->getLogFilePath());
    }

    public function testAdd()
    {
        $this->varReaderWriter->expects($this->at(0))->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->at(1))->method('writeFile');
        $this->varReaderWriter->expects($this->at(2))->method('isExist')->willReturn(true);
        $this->varReaderWriter->expects($this->at(3))->method('readFile')->willReturn('test0');
        $this->varReaderWriter->expects($this->at(4))->method('writeFile');
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

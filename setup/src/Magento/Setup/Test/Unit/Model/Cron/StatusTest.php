<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Setup\Model\Cron\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Status
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\SetupLoggerFactory
     */
    private $setupLoggerFactory;

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
        $this->logger = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface', [], '', false);
        $this->setupLoggerFactory = $this->getMock('\Magento\Setup\Model\Cron\SetupLoggerFactory', [], [], '', false);
        $this->setupLoggerFactory
            ->expects($this->once())
            ->method('create')
            ->with('setup-cron')
            ->willReturn($this->logger);
        $this->status = new Status($this->filesystem, $this->setupLoggerFactory);
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
            ->with('log/update.log')
            ->willReturn('DIR/var/log/update.log');
        $this->assertEquals('DIR/var/log/update.log', $this->status->getLogFilePath());
    }

    public function testAdd()
    {
        $this->varReaderWriter->expects($this->once())->method('isExist')->willReturn(false);
        $this->varReaderWriter->expects($this->once())->method('writeFile');
        $this->logger->expects($this->once())->method('log')->with(\Psr\Log\LogLevel::ERROR, 'test1');
        $this->status->add('test1', \Psr\Log\LogLevel::ERROR);
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

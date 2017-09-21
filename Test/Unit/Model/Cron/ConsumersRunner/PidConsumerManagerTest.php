<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Test\Unit\Model\Cron\ConsumersRunner;

use Magento\MessageQueue\Model\Cron\ConsumersRunner\PidConsumerManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Write;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class PidConsumerManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var DirectoryList|MockObject
     */
    private $directoryListMock;

    /**
     * @var WriteFactory|MockObject
     */
    private $writeFactoryMock;

    /**
     * @var PidConsumerManager
     */
    private $pidConsumerManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        require_once __DIR__ . '/../../../_files/pid_consumer_functions_mocks.php';

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeFactoryMock = $this->getMockBuilder(WriteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pidConsumerManager = new PidConsumerManager(
            $this->filesystemMock,
            $this->writeFactoryMock,
            $this->directoryListMock
        );
    }

    /**
     * @param bool $fileExists
     * @param int|null $pid
     * @param bool $expectedResult
     * @dataProvider isRunDataProvider
     */
    public function testIsRun($fileExists, $pid, $expectedResult)
    {
        $consumerName = 'consumerName';
        $pidFile = $consumerName . PidConsumerManager::PID_FILE_EXT;

        /** @var WriteInterface|MockObject $directoryMock */
        $directoryMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn($fileExists);
        $directoryMock->expects($this->any())
            ->method('readFile')
            ->with($pidFile)
            ->willReturn($pid);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryMock);

        $this->assertSame($expectedResult, $this->pidConsumerManager->isRun($consumerName));
    }

    /**
     * @return array
     */
    public function isRunDataProvider()
    {
        return [
            ['fileExists' => false, 'pid' => null, false],
            ['fileExists' => false, 'pid' => 11111, false],
            ['fileExists' => true, 'pid' => 11111, true],
            ['fileExists' => true, 'pid' => 77777, false],
        ];
    }

    public function testGetPidFilePath()
    {
        $consumerName = 'consumerName';
        $varPath = '/magento/var';
        $expectedResult = $varPath . '/' . $consumerName . PidConsumerManager::PID_FILE_EXT;

        $this->directoryListMock->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($varPath);

        $this->assertSame($expectedResult, $this->pidConsumerManager->getPidFilePath($consumerName));
    }

    public function testSavePid()
    {
        $pidFilePath = '/var/somePath/pidfile.pid';

        /** @var Write|\PHPUnit_Framework_MockObject_MockObject $writeMock */
        $writeMock = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->getMock();
        $writeMock->expects($this->once())
            ->method('write')
            ->with(function_exists('posix_getpid') ? posix_getpid() : getmypid());
        $writeMock->expects($this->once())
            ->method('close');

        $this->writeFactoryMock->expects($this->once())
            ->method('create')
            ->with($pidFilePath, DriverPool::FILE, 'w')
            ->willReturn($writeMock);

        $this->pidConsumerManager->savePid($pidFilePath);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Test\Unit\Model\Cron\ConsumersRunner;

use Magento\MessageQueue\Model\Cron\ConsumersRunner\PidConsumerManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class PidConsumerManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

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

        $this->pidConsumerManager = new PidConsumerManager($this->filesystemMock);
    }

    /**
     * @param bool $fileExists
     * @param int|null $pid
     * @param bool $expectedResult
     * @dataProvider isRunDataProvider
     */
    public function testIsRun($fileExists, $pid, $expectedResult)
    {
        $pidFilePath = 'somepath/consumerName.pid';

        /** @var ReadInterface|MockObject $directoryMock */
        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $directoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn($fileExists);
        $directoryMock->expects($this->any())
            ->method('readFile')
            ->with($pidFilePath)
            ->willReturn($pid);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryMock);

        $this->assertSame($expectedResult, $this->pidConsumerManager->isRun($pidFilePath));
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

    public function testSavePid()
    {
        $pidFilePath = '/var/somePath/pidfile.pid';

        /** @var WriteInterface|MockObject $writeMock */
        $writeMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($writeMock);
        $writeMock->expects($this->once())
            ->method('writeFile')
            ->with(
                $pidFilePath,
                function_exists('posix_getpid') ? posix_getpid() : getmypid(),
                'w'
            );

        $this->pidConsumerManager->savePid($pidFilePath);
    }
}

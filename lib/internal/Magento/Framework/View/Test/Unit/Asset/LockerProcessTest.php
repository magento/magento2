<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\LockerProcess;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see \Magento\Framework\View\Asset\LockerProcess
 */
class LockerProcessTest extends TestCase
{
    const LOCK_NAME = 'test-lock';

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var LockerProcess
     */
    private $lockerProcess;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->fileName = DirectoryList::TMP . DIRECTORY_SEPARATOR . self::LOCK_NAME . LockerProcess::LOCK_EXTENSION;

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->lockerProcess = (new ObjectManager($this))->getObject(
            LockerProcess::class,
            [
                'filesystem' => $this->filesystemMock,
                'state' => $this->stateMock,
            ]
        );
    }

    public function testNotLockProcessInProductionMode()
    {
        $this->stateMock->expects(self::once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesystemMock->expects($this->never())->method('getDirectoryWrite');

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
    }

    public function testNotUnlockProcessInProductionMode()
    {
        $this->stateMock->expects(self::exactly(2))->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesystemMock->expects(self::never())->method('getDirectoryWrite');

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
        $this->lockerProcess->unlockProcess();
    }

    /**
     * @return WriteInterface|MockObject
     */
    protected function getTmpDirectoryMockTrue()
    {
        $tmpDirectoryMock = $this->getTmpDirectoryMock();

        $tmpDirectoryMock->expects(self::atLeastOnce())
            ->method('isExist')
            ->with($this->fileName)
            ->willReturn(true);

        $tmpDirectoryMock->expects(self::atLeastOnce())
            ->method('readFile')
            ->with($this->fileName)
            ->willReturn(time() - 25);

        $tmpDirectoryMock->expects(self::once())
            ->method('writeFile')
            ->with($this->fileName, self::matchesRegularExpression('#\d+#'));

        return $tmpDirectoryMock;
    }

    /**
     * @param int $exactly
     * @return WriteInterface|MockObject
     */
    protected function getTmpDirectoryMockFalse($exactly = 0)
    {
        $tmpDirectoryMock = $this->getTmpDirectoryMock();

        $tmpDirectoryMock->expects(self::atLeastOnce())
            ->method('isExist')
            ->with($this->fileName)
            ->willReturn(false);

        $tmpDirectoryMock->expects(self::never())
            ->method('readFile');

        $tmpDirectoryMock->expects(self::exactly($exactly))
            ->method('delete')
            ->with($this->fileName);

        $tmpDirectoryMock->expects(self::once())
            ->method('writeFile')
            ->with($this->fileName, self::matchesRegularExpression('#\d+#'));

        return $tmpDirectoryMock;
    }

    /**
     * @return WriteInterface|MockObject
     */
    private function getTmpDirectoryMock()
    {
        $tmpDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        return $tmpDirectoryMock;
    }
}

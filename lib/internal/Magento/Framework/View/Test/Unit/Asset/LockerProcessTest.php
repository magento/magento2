<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\LockerProcess;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LockerProcessTest
 *
 * @see \Magento\Framework\View\Asset\LockerProcess
 */
class LockerProcessTest extends \PHPUnit\Framework\TestCase
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
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->fileName = DirectoryList::TMP . DIRECTORY_SEPARATOR . self::LOCK_NAME . LockerProcess::LOCK_EXTENSION;

        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
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

    /**
     * Test for lockProcess method
     *
     * @param string $method
     *
     * @dataProvider dataProviderTestLockProcess
     */
    public function testLockProcess($method)
    {
        $this->stateMock->expects($this->once())->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->$method());

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
    }

    public function testNotLockProcessInProductionMode()
    {
        $this->stateMock->expects($this->once())->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesystemMock->expects($this->never())->method('getDirectoryWrite');

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
    }

    /**
     * Test for unlockProcess method
     */
    public function testUnlockProcess()
    {
        $this->stateMock->expects($this->exactly(2))->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->getTmpDirectoryMockFalse(1));

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
        $this->lockerProcess->unlockProcess();
    }

    public function testNotUnlockProcessInProductionMode()
    {
        $this->stateMock->expects($this->exactly(2))->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->filesystemMock->expects($this->never())->method('getDirectoryWrite');

        $this->lockerProcess->lockProcess(self::LOCK_NAME);
        $this->lockerProcess->unlockProcess();
    }

    /**
     * @return array
     */
    public function dataProviderTestLockProcess()
    {
        return [
            ['method' => 'getTmpDirectoryMockTrue'],
            ['method' => 'getTmpDirectoryMockFalse']
        ];
    }

    /**
     * @return WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTmpDirectoryMockTrue()
    {
        $tmpDirectoryMock = $this->getTmpDirectoryMock();

        $tmpDirectoryMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with($this->fileName)
            ->willReturn(true);

        $tmpDirectoryMock->expects($this->atLeastOnce())
            ->method('readFile')
            ->with($this->fileName)
            ->willReturn(time() - 25);

        $tmpDirectoryMock->expects($this->once())
            ->method('writeFile')
            ->with($this->fileName, $this->matchesRegularExpression('#\d+#'));

        return $tmpDirectoryMock;
    }

    /**
     * @param int $exactly
     * @return WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTmpDirectoryMockFalse($exactly = 0)
    {
        $tmpDirectoryMock = $this->getTmpDirectoryMock();

        $tmpDirectoryMock->expects($this->atLeastOnce())
            ->method('isExist')
            ->with($this->fileName)
            ->willReturn(false);

        $tmpDirectoryMock->expects($this->never())
            ->method('readFile');

        $tmpDirectoryMock->expects($this->exactly($exactly))
            ->method('delete')
            ->with($this->fileName);

        $tmpDirectoryMock->expects($this->once())
            ->method('writeFile')
            ->with($this->fileName, $this->matchesRegularExpression('#\d+#'));

        return $tmpDirectoryMock;
    }

    /**
     * @return WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTmpDirectoryMock()
    {
        $tmpDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->getMockForAbstractClass();

        return $tmpDirectoryMock;
    }
}

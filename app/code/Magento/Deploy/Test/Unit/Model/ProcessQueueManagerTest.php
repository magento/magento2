<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

class ProcessQueueManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\ProcessQueueManager
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Deploy\Model\ProcessManager
     */
    private $processManagerMock;

    protected function setUp()
    {
        $this->processManagerMock = $this->getMock(\Magento\Deploy\Model\ProcessManager::class, [], [], '', false);
        $this->model = new \Magento\Deploy\Model\ProcessQueueManager($this->processManagerMock);
    }

    public function testProcess()
    {
        $callableMock = function () {
            return true;
        };

        $processMock = $this->getMock(\Magento\Deploy\Model\Process::class, [], [], '', false);

        $this->model->addTaskToQueue($callableMock, []);
        $this->processManagerMock->expects($this->atLeastOnce())->method('getProcesses')->willReturnOnConsecutiveCalls(
            [$processMock],
            [$processMock],
            [$processMock],
            [$processMock],
            [$processMock],
            []
        );
        $processMock->expects($this->once())->method('isCompleted')->willReturn(true);
        $processMock->expects($this->atLeastOnce())->method('getPid')->willReturn(42);
        $processMock->expects($this->once())->method('getStatus')->willReturn(0);
        $this->processManagerMock->expects($this->once())->method('delete')->with($processMock);

        $this->assertEquals(0, $this->model->process());
    }
}

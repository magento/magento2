<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Log\Console\Command\LogCleanCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\ObjectManager;

class LogCleanCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($this->objectManager);
        $this->commandTester = new CommandTester(new LogCleanCommand($objectManagerFactory));
    }

    public function testExecute()
    {
        $mutableConfig = $this->getMock('Magento\Framework\App\Config\MutableScopeConfigInterface', [], [], '', false);
        $logFactory = $this->getMock('Magento\Log\Model\LogFactory', [], [], '', false);
        $returnValueMap = [
            [
                'Magento\Framework\App\Config\MutableScopeConfigInterface',
                [],
                $mutableConfig,
            ],
            [
                'Magento\Log\Model\LogFactory',
                [],
                $logFactory,
            ],
        ];
        $this->objectManager
            ->expects($this->exactly(2))->method('create')
            ->will($this->returnValueMap($returnValueMap));
        $mutableConfig->expects($this->once())->method('setValue');
        $log = $this->getMock('Magento\Log\Model\Log', [], [], '', false);
        $logFactory->expects($this->once())->method('create')->willReturn($log);
        $log->expects($this->once())->method('clean');
        $this->commandTester->execute(['--days' => '1']);
        $this->assertEquals('Log cleaned.' . PHP_EOL, $this->commandTester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid value for option "days"
     */
    public function testExecuteInvalidNegativeDays()
    {
        $this->commandTester->execute(['--days' => '-1']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid value for option "days"
     */
    public function testExecuteInvalidFractionDays()
    {
        $this->commandTester->execute(['--days' => '5.5']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid value for option "days"
     */
    public function testExecuteInvalidTexyDays()
    {
        $this->commandTester->execute(['--days' => 'test']);
    }
}

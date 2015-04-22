<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Log\Console\Command\LogCleanCommand;
use Symfony\Component\Console\Tester\CommandTester;

class LogCleanCommandTest extends \PHPUnit_Framework_TestCase
{
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
        $objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManager->expects($this->exactly(2))->method('create')->will($this->returnValueMap($returnValueMap));
        $mutableConfig->expects($this->once())->method('setValue');
        $log = $this->getMock('Magento\Log\Model\Log', [], [], '', false);
        $logFactory->expects($this->once())->method('create')->willReturn($log);
        $log->expects($this->once())->method('clean');
        $objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactory->expects($this->once())->method('create')->willReturn($objectManager);
        $commandTester = new CommandTester(new LogCleanCommand($objectManagerFactory));
        $commandTester->execute(['--days' => '1']);
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
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
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', [], [], '', false);
        $state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $configLoader->expects($this->once())
            ->method('load')
            ->with(FrontNameResolver::AREA_CODE)
            ->will($this->returnValue([]));
        $state->expects($this->once())
            ->method('setAreaCode')
            ->with(FrontNameResolver::AREA_CODE);
        $this->commandTester = new CommandTester(new LogCleanCommand($this->objectManager, $configLoader, $state));
    }

    public function testExecute()
    {
        $mutableConfig = $this->getMock('Magento\Framework\App\Config\MutableScopeConfigInterface', [], [], '', false);
        $logFactory = $this->getMockBuilder('Magento\Log\Model\LogFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
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
     *
     * @param string $days
     * @dataProvider daysDataProvider
     */
    public function testExecuteInvalidNegativeDays($days)
    {
        $this->commandTester->execute(['--days' => $days]);
        //Invalid value for option "days". It should be a whole number greater than 0.
        $this->assertEquals(
            'Invalid value for option "days". It should be a whole number greater than 0.' . PHP_EOL,
            $this->commandTester->getDisplay()
        );
    }

    /**
     * @return array
     */
    public function daysDataProvider()
    {
        return [['-1'], ['5.5'], ['test']];
    }
}

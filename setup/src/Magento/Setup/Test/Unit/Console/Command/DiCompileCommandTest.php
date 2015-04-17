<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DiCompileCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DiCompileCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Setup\Module\Di\App\Task\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var DiCompileCommand
     */
    private $command;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProviderr',
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->manager = $this->getMock('Magento\Setup\Module\Di\App\Task\Manager', [], [], '', false);
        $this->command = new DiCompileCommand($this->deploymentConfig, $this->manager, $this->objectManagerProvider);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals('Application is not installed yet.' . PHP_EOL , $tester->getDisplay());
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->objectManager->expects($this->once())->method('configure');
        $this->manager->expects($this->exactly(5))->method('addOperation');
        $this->manager->expects($this->once())->method('process');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals('Successful' . PHP_EOL, $tester->getDisplay());
    }
}

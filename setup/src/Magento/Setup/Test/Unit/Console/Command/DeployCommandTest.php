<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DeployCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DeployCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var Magento\Framework\Console\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Setup\Model\Deployer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deployer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var DeployCommand
     */
    private $command;

    protected function setUp()
    {
        $this->objectManagerProvider = $this->getMock('Magento\Framework\Console\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->deployer = $this->getMock('Magento\Setup\Model\Deployer', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->command = new DeployCommand($this->objectManagerProvider, $this->deploymentConfig);
    }

    public function testExecute()
    {
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->willReturn($this->deployer);

        $this->objectManagerProvider->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->objectManager));

        $this->objectManagerProvider->expects($this->once())->method('getObjectManagerFactory')->with([]);

        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(false));
        $this->objectManagerProvider->expects($this->never())->method('get');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat(
            'You need to install the Magento application before running this utility.%w',
            $tester->getDisplay()
        );
    }
}


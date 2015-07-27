<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\DeployStaticContentCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Validator\Locale;

class DeployStaticContentCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Deploy\Deployer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deployer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ObjectManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerFactory;

    /**
     * @var \Magento\Framework\App\Utility\Files|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesUtil;

    /**
     * @var DeployStaticContentCommand
     */
    private $command;

    /**
     * @var Locale|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    protected function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $this->deployer = $this->getMock('Magento\Deploy\Deployer', [], [], '', false);
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->filesUtil = $this->getMock('Magento\Framework\App\Utility\Files', [], [], '', false);
        $this->validator = $this->getMock('Magento\Framework\Validator\Locale', [], [], '', false);
        $this->command = new DeployStaticContentCommand(
            $this->objectManagerFactory,
            $this->deploymentConfig,
            $this->validator
        );
    }

    public function testExecute()
    {
        $omFactory = $this->getMock('Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $this->objectManagerFactory->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->objectManager));

        $this->objectManagerFactory->expects($this->once())
            ->method('getObjectManagerFactory')
            ->with([])
            ->willReturn($omFactory);

        $this->deployer->expects($this->once())->method('deploy');

        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->willReturn($this->filesUtil);

        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->willReturn($this->deployer);

        $this->validator->expects($this->once())->method('isValid')->with('en_US')->willReturn(true);

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
        $this->objectManagerFactory->expects($this->never())->method('get');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringMatchesFormat(
            'You need to install the Magento application before running this utility.%w',
            $tester->getDisplay()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ARG_IS_WRONG argument has invalid value, please run info:language:list
     */
    public function testExecuteInvalidLanguageArgument()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $wrongParam = ['languages' => ['ARG_IS_WRONG']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($wrongParam);
    }
}

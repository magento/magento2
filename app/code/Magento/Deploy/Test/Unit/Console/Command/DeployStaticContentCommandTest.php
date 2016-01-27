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
     * @var \Magento\Deploy\Model\Deployer|\PHPUnit_Framework_MockObject_MockObject
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
        $this->deployer = $this->getMock('Magento\Deploy\Model\Deployer', [], [], '', false);
        $this->filesUtil = $this->getMock('Magento\Framework\App\Utility\Files', [], [], '', false);
        $this->validator = $this->getMock('Magento\Framework\Validator\Locale', [], [], '', false);
        $this->command = new DeployStaticContentCommand(
            $this->objectManagerFactory,
            $this->validator,
            $this->objectManager
        );
    }

    public function testExecute()
    {
        $this->deployer->expects($this->once())->method('deploy');

        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->willReturn($this->filesUtil);

        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->willReturn($this->deployer);

        $this->validator->expects($this->once())->method('isValid')->with('en_US')->willReturn(true);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ARG_IS_WRONG argument has invalid value, please run info:language:list
     */
    public function testExecuteInvalidLanguageArgument()
    {
        $wrongParam = ['languages' => ['ARG_IS_WRONG']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($wrongParam);
    }
}

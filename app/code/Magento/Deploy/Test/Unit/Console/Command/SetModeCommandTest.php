<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\SetModeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\State;

/**
 * @package Magento\Deploy\Test\Unit\Console\Command
 */
class SetModeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\Mode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modeMock;

    /**
     * @var SetModeCommand
     */
    private $command;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->modeMock = $this->getMock('Magento\Deploy\Model\Mode', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->command = $objectManager->getObject(
            'Magento\Deploy\Console\Command\SetModeCommand',
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())->method('create')->willReturn($this->modeMock);
    }

    public function testSetProductionMode()
    {
        $this->modeMock->expects($this->once())->method('enableProductionMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production']);
        $this->assertContains(
            "production mode",
            $tester->getDisplay()
        );
    }

    public function testSetDeveloperMode()
    {
        $this->modeMock->expects($this->once())->method('enableDeveloperMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'developer']);
        $this->assertContains(
            "developer mode",
            $tester->getDisplay()
        );
    }

    public function testSetProductionSkipCompilation()
    {
        $this->modeMock->expects($this->once())->method('enableProductionModeMinimal');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production', '--skip-compilation' => true]);
        $this->assertContains(
            "production mode",
            $tester->getDisplay()
        );
    }

    public function testSetInvalidMode()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'invalid-mode']);
        $this->assertContains(
            "Cannot switch into given mode",
            $tester->getDisplay()
        );
    }
}

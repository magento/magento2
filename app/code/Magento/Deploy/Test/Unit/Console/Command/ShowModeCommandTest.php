<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\ShowModeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\State;

/**
 * @package Magento\Deploy\Test\Unit\Console\Command
 */
class ShowModeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Deploy\Model\Mode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modeMock;

    /**
     * @var ShowModeCommand
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
            'Magento\Deploy\Console\Command\ShowModeCommand',
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())->method('create')->willReturn($this->modeMock);
    }

    public function testExecute()
    {
        $currentMode = 'application-mode';
        $this->modeMock->expects($this->once())->method('getMode')->willReturn($currentMode);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertContains(
            $currentMode,
            $tester->getDisplay()
        );
    }
}

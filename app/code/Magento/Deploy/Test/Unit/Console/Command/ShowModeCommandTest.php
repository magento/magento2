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
 * Class ShowModeCommandTest
 * Test for ShowModeCommand
 */
class ShowModeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Deploy\Model\Mode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $modeMock;

    /**
     * @var ShowModeCommand
     */
    private $command;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->modeMock = $this->createMock(\Magento\Deploy\Model\Mode::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->command = $objectManager->getObject(
            \Magento\Deploy\Console\Command\ShowModeCommand::class,
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
        $this->assertStringContainsString($currentMode, $tester->getDisplay());
    }
}

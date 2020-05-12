<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\ShowModeCommand;
use Magento\Deploy\Model\Mode;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ShowModeCommandTest extends TestCase
{
    /**
     * @var Mode|MockObject
     */
    private $modeMock;

    /**
     * @var ShowModeCommand
     */
    private $command;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->modeMock = $this->createMock(Mode::class);

        $objectManager = new ObjectManager($this);
        $this->command = $objectManager->getObject(
            ShowModeCommand::class,
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
        $this->assertStringContainsString(
            $currentMode,
            $tester->getDisplay()
        );
    }
}

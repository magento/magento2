<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\SetModeCommand;
use Magento\Deploy\Model\Mode;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SetModeCommandTest extends TestCase
{
    /**
     * @var Mode|MockObject
     */
    private $modeMock;

    /**
     * @var SetModeCommand
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
            SetModeCommand::class,
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())->method('create')->willReturn($this->modeMock);
    }

    public function testSetProductionMode()
    {
        $this->modeMock->expects($this->once())->method('enableProductionMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production']);
        $this->assertStringContainsString(
            "production mode",
            $tester->getDisplay()
        );
    }

    public function testSetDeveloperMode()
    {
        $this->modeMock->expects($this->once())->method('enableDeveloperMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'developer']);
        $this->assertStringContainsString(
            "developer mode",
            $tester->getDisplay()
        );
    }

    public function testSetDefaultMode()
    {
        $this->modeMock->expects($this->once())->method('enableDefaultMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'default']);
        $this->assertStringContainsString(
            "default mode",
            $tester->getDisplay()
        );
    }

    public function testSetProductionSkipCompilation()
    {
        $this->modeMock->expects($this->once())->method('enableProductionModeMinimal');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production', '--skip-compilation' => true]);
        $this->assertStringContainsString(
            "production mode",
            $tester->getDisplay()
        );
    }

    public function testSetInvalidMode()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'invalid-mode']);
        $this->assertStringContainsString(
            'The mode can\'t be switched to "invalid-mode".',
            $tester->getDisplay()
        );
    }
}

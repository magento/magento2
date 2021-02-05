<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Test\Unit\Console\Command;

use Magento\Deploy\Console\Command\SetModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class SetModeCommandTest
 * Test for SetModeCommandTest
 */
class SetModeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Deploy\Model\Mode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $modeMock;

    /**
     * @var SetModeCommand
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
            \Magento\Deploy\Console\Command\SetModeCommand::class,
            ['objectManager' => $this->objectManagerMock]
        );

        $this->objectManagerMock->expects($this->once())->method('create')->willReturn($this->modeMock);
    }

    public function testSetProductionMode()
    {
        $this->modeMock->expects($this->once())->method('enableProductionMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production']);
        $this->assertStringContainsString("production mode", $tester->getDisplay());
    }

    public function testSetDeveloperMode()
    {
        $this->modeMock->expects($this->once())->method('enableDeveloperMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'developer']);
        $this->assertStringContainsString("developer mode", $tester->getDisplay());
    }

    public function testSetDefaultMode()
    {
        $this->modeMock->expects($this->once())->method('enableDefaultMode');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'default']);
        $this->assertStringContainsString("default mode", $tester->getDisplay());
    }

    public function testSetProductionSkipCompilation()
    {
        $this->modeMock->expects($this->once())->method('enableProductionModeMinimal');

        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'production', '--skip-compilation' => true]);
        $this->assertStringContainsString("production mode", $tester->getDisplay());
    }

    public function testSetInvalidMode()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['mode' => 'invalid-mode']);
        $this->assertStringContainsString('The mode can\'t be switched to "invalid-mode".', $tester->getDisplay());
    }
}

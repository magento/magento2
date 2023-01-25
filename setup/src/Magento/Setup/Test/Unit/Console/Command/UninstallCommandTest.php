<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\UninstallCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class UninstallCommandTest extends TestCase
{
    /**
     * @var InstallerFactory|MockObject
     */
    private $installerFactory;

    /**
     * @var Installer|MockObject
     */
    private $installer;

    /**
     * @var UninstallCommand|MockObject
     */
    private $command;

    protected function setUp(): void
    {
        $this->installerFactory = $this->createMock(InstallerFactory::class);
        $this->installer = $this->createMock(Installer::class);
        $this->command = new UninstallCommand($this->installerFactory);
    }

    public function testExecuteInteractionYes()
    {
        $this->installer->expects($this->once())->method('uninstall');
        $this->installerFactory->expects($this->once())->method('create')->willReturn($this->installer);

        $this->checkInteraction(true);
    }

    public function testExecuteInteractionNo()
    {
        $this->installer->expects($this->exactly(0))->method('uninstall');
        $this->installerFactory->expects($this->exactly(0))->method('create');

        $this->checkInteraction(false);
    }

    /**
     * @param $answer
     */
    public function checkInteraction($answer)
    {
        $question = $this->createMock(QuestionHelper::class);
        $question
            ->expects($this->once())
            ->method('ask')
            ->willReturn($answer);

        /** @var HelperSet|MockObject $helperSet */
        $helperSet = $this->createMock(HelperSet::class);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->willReturn($question);
        $this->command->setHelperSet($helperSet);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }
}

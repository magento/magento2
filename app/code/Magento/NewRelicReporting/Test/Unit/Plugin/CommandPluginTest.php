<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Plugin\CommandPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CommandPluginTest extends TestCase
{
    private const STUB_SKIPPED_COMMAND_NAME = 'skippedCommand';
    private const STUB_NON_SKIPPED_COMMAND_NAME = 'nonSkippedCommand';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject|NewRelicWrapper
     */
    private $newRelicWrapperMock;

    /**
     * ObjectManager and mocks necessary to run the tests
     */
    protected function setUp(): void
    {
        $this->newRelicWrapperMock = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setTransactionName'])
            ->getMock();

        $this->objectManager = new ObjectManager($this);
    }

    /**
     * When Command name is not in the list of skipped, handle New Relic transaction
     */
    public function testNewRelicTransactionSetForNonSkippedCommand()
    {
        $nonSkippedCommand = $this->getCommandMock(self::STUB_NON_SKIPPED_COMMAND_NAME);

        $this->newRelicWrapperMock->expects($this->once())
            ->method('setTransactionName')
            ->with(sprintf('CLI %s', self::STUB_NON_SKIPPED_COMMAND_NAME));

        $commandPlugin = $this->getCommandPlugin([self::STUB_SKIPPED_COMMAND_NAME => true]);
        $commandPlugin->beforeRun($nonSkippedCommand);
    }

    /**
     * When Command name is set to be skipped, do not let run New Relic transaction
     */
    public function testNewRelicTransactionOmmitForSkippedCommand()
    {
        $skippedCommand = $this->getCommandMock(self::STUB_SKIPPED_COMMAND_NAME);

        $this->newRelicWrapperMock->expects($this->never())
            ->method('setTransactionName');

        $commandPlugin = $this->getCommandPlugin([self::STUB_SKIPPED_COMMAND_NAME => true]);
        $commandPlugin->beforeRun($skippedCommand);
    }

    /**
     * @param string $commandName
     * @return Command|MockObject
     */
    private function getCommandMock(string $commandName): Command
    {
        $commandMock = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();

        $commandMock->method('getName')
            ->willReturn($commandName);

        return $commandMock;
    }

    /**
     * @param string[] $skippedCommands
     * @return CommandPlugin
     */
    private function getCommandPlugin(array $skippedCommands): CommandPlugin
    {
        /** @var CommandPlugin $commandPlugin */
        $commandPlugin = $this->objectManager->getObject(CommandPlugin::class, [
            'skipCommands' => $skippedCommands,
            'newRelicWrapper' => $this->newRelicWrapperMock
        ]);

        return $commandPlugin;
    }
}

<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console;

use Laminas\ServiceManager\ServiceManager;
use Magento\Setup\Console\CommandLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CommandLoaderTest extends TestCase
{
    /**
     * @var MockObject|CommandLoader
     */
    private MockObject|CommandLoader $commandLoader;

    /**
     * @var MockObject|ServiceManager
     */
    private ServiceManager|MockObject $serviceManager;

    protected function setUp(): void
    {
        $this->serviceManager = $this->createMock(ServiceManager::class);
        $this->commandLoader = new CommandLoader($this->serviceManager);
    }

    public function testServiceManagerIsUsedToInitializeCommands(): void
    {
        $command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serviceManager->expects($this->once())
            ->method('get')
            ->willReturn($command);

        $firstCommandName = current($this->commandLoader->getNames());
        $this->commandLoader->get($firstCommandName);
    }
}

<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Console;

use Magento\Framework\Console\CommandLoader;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLoaderTest extends TestCase
{
    /** @var MockObject|ObjectManagerInterface */
    private ObjectManagerInterface|MockObject $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
    }

    /**
     * Test that the command loader, when provided zero commands, does not have a command named "foo"
     */
    public function testHasWithZeroCommands(): void
    {
        $subj = new CommandLoader($this->objectManagerMock, []);

        $this->assertFalse($subj->has('foo'));
    }

    /**
     * Test that the command loader will return true when provided with a command "foo"
     */
    public function testHasWithAtLeastOneCommand(): void
    {
        $subj = new CommandLoader($this->objectManagerMock, [
            [
                'name' => 'foo',
                'class' => FooCommand::class
            ]
        ]);

        $this->assertTrue($subj->has('foo'));
    }

    /**
     * Test that the command loader will throw a CommandNotFoundException when it does not have the requested command
     */
    public function testGetWithZeroCommands(): void
    {
        $subj = new CommandLoader($this->objectManagerMock, []);

        $this->expectException(CommandNotFoundException::class);

        $subj->get('foo');
    }

    /**
     * Test that the command loader returns a command when one it has is requested
     */
    public function testGetWithAtLeastOneCommand(): void
    {
        $this->objectManagerMock
            ->method('create')
            ->with(FooCommand::class)
            ->willReturn(new FooCommand());

        $subj = new CommandLoader($this->objectManagerMock, [
            [
                'name' => 'foo',
                'class' => FooCommand::class
            ]
        ]);

        $this->assertInstanceOf(FooCommand::class, $subj->get('foo'));
    }

    /**
     * Test that the command loader will return an empty "names" array when it has none
     */
    public function testGetNamesWithZeroCommands(): void
    {
        $subj = new CommandLoader($this->objectManagerMock, []);

        $this->assertEquals([], $subj->getNames());
    }

    /**
     * Test that the command loader returns an array of its command names when `getNames` is called
     */
    public function testGetNames(): void
    {
        $subj = new CommandLoader($this->objectManagerMock, [
            [
                'name' => 'foo',
                'class' => FooCommand::class
            ],
            [
                'name' => 'bar',
                'class' => 'BarCommand'
            ]
        ]);

        $this->assertEquals(['foo', 'bar'], $subj->getNames());
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration
class FooCommand extends Command
{
}

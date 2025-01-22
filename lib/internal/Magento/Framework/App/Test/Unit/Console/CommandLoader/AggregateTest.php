<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Console\CommandLoader;

use Magento\Framework\Console\CommandLoader\Aggregate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Tests the "aggregate" command loader
 * @see Aggregate
 */
class AggregateTest extends TestCase
{
    /** @var CommandLoaderInterface|MockObject */
    private MockObject|CommandLoaderInterface $firstMockCommandLoader;

    /** @var CommandLoaderInterface|MockObject */
    private MockObject|CommandLoaderInterface $secondMockCommandLoader;

    /** @var Aggregate */
    private Aggregate $aggregateCommandLoader;

    protected function setUp(): void
    {
        $this->firstMockCommandLoader = $this->getMockBuilder(CommandLoaderInterface::class)->getMock();
        $this->secondMockCommandLoader = $this->getMockBuilder(CommandLoaderInterface::class)->getMock();
        $this->aggregateCommandLoader = new Aggregate([$this->firstMockCommandLoader, $this->secondMockCommandLoader]);
    }

    /**
     * Test the various cases of `has` for the aggregate command loader:
     *  - When at least one "internal" command loader has a command, the aggregate does as well
     *  - When none of the "internal" command loaders has a command, neither does the aggregate
     *
     * @dataProvider provideTestCasesForHas
     */
    public function testHas(bool $firstResult, bool $secondResult, bool $overallResult): void
    {
        $this->firstMockCommandLoader->method('has')->with('foo')->willReturn($firstResult);
        $this->secondMockCommandLoader->method('has')->with('foo')->willReturn($secondResult);

        $this->assertEquals($overallResult, $this->aggregateCommandLoader->has('foo'));
    }

    public function provideTestCasesForHas(): array
    {
        return [
            [true, false, true],
            [false, true, true],
            [false, false, false]
        ];
    }

    /**
     * Test the various cases of `get` for the aggregate command loader. Similar to `has`,
     * the return value of `Aggregate::get` mirrors its internal command loaders.
     *
     * For simplicity, this test does not cover the "no results" case. @see testGetThrow
     *
     * @dataProvider provideTestCasesForGet
     */
    public function testGet(?Command $firstCmd, ?Command $secondCmd): void
    {
        $firstHas = (bool)$firstCmd;
        $secondHas = (bool)$secondCmd;

        $this->firstMockCommandLoader->method('has')->with('foo')->willReturn($firstHas);
        if ($firstHas) {
            $this->firstMockCommandLoader->method('get')->with('foo')->willReturn($firstCmd);
        }

        $this->secondMockCommandLoader->method('has')->with('foo')->willReturn($secondHas);
        if ($secondHas) {
            $this->secondMockCommandLoader->method('get')->with('foo')->willReturn($secondCmd);
        }

        $this->assertInstanceOf(Command::class, $this->aggregateCommandLoader->get('foo'));
    }

    public function provideTestCasesForGet(): array
    {
        return [
            [
                new Command(),
                null
            ],
            [
                null,
                new Command()
            ]
        ];
    }

    /**
     * When none of the internal command loaders have matching commands, the aggregate command loader
     * will throw an exception. @see CommandNotFoundException
     */
    public function testGetThrow(): void
    {
        $this->firstMockCommandLoader->method('has')->with('foo')->willReturn(false);
        $this->secondMockCommandLoader->method('has')->with('foo')->willReturn(false);

        $this->expectException(CommandNotFoundException::class);
        $this->aggregateCommandLoader->get('foo');
    }

    /**
     * An aggregate command loader's `getNames` method returns the merged array of the `getNames`
     * return values of all its internal command loaders
     */
    public function testGetNames(): void
    {
        $this->firstMockCommandLoader->method('getNames')->willReturn(['foo', 'bar']);
        $this->secondMockCommandLoader->method('getNames')->willReturn(['baz', 'qux']);

        $this->assertEquals(['foo', 'bar', 'baz', 'qux'], $this->aggregateCommandLoader->getNames());
    }
}

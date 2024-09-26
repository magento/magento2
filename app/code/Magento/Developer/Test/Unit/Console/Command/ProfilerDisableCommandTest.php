<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\ProfilerDisableCommand;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ProfilerDisableCommandTest
 *
 * Tests dev:profiler:disable command.
 */
class ProfilerDisableCommandTest extends TestCase
{
    /**
     * @var File|MockObject
     */
    private $filesystemMock;

    /**
     * Test disabling the profiler by command.
     *
     * @param bool $fileExists
     * @param string $expectedOutput
     * @dataProvider commandDataProvider
     */
    public function testCommand(bool $fileExists, string $expectedOutput)
    {
        $this->filesystemMock
            ->expects($this->once())
            ->method('rm')
            ->with(BP . '/' . ProfilerDisableCommand::PROFILER_FLAG_FILE);
        $this->filesystemMock
            ->expects($this->once())
            ->method('fileExists')
            ->with(BP . '/' . ProfilerDisableCommand::PROFILER_FLAG_FILE)
            ->willReturn($fileExists);
        /** @var ProfilerDisableCommand $command */
        $command = new ProfilerDisableCommand($this->filesystemMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals(
            $expectedOutput,
            trim(str_replace(PHP_EOL, ' ', $commandTester->getDisplay()))
        );
    }

    /**
     * Data provider for testCommand.
     *
     * @return array
     */
    public static function commandDataProvider()
    {
        return [
            [true, 'Something went wrong while disabling the profiler.'],
            [false, 'Profiler disabled.'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

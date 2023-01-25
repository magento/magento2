<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\ProfilerEnableCommand;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ProfilerEnableCommandTest
 *
 * Tests dev:profiler:enable command.
 */
class ProfilerEnableCommandTest extends TestCase
{
    /**
     * @var File|MockObject
     */
    private $filesystemMock;

    /**
     * Tests enabling the profiler by command.
     *
     * @param string $inputType
     * @param bool $fileExists
     * @param string $expectedOutput
     * @dataProvider commandDataProvider
     */
    public function testCommand(string $inputType, bool $fileExists, string $expectedOutput)
    {
        $this->filesystemMock
            ->expects($this->once())
            ->method('write')
            ->with(
                BP . '/' . ProfilerEnableCommand::PROFILER_FLAG_FILE,
                $inputType ?: ProfilerEnableCommand::TYPE_DEFAULT
            );
        $this->filesystemMock
            ->expects($this->once())
            ->method('fileExists')
            ->with(BP . '/' . ProfilerEnableCommand::PROFILER_FLAG_FILE)
            ->willReturn($fileExists);
        /** @var ProfilerEnableCommand $command */
        $command = new ProfilerEnableCommand($this->filesystemMock);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['type' => $inputType]);

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
    public function commandDataProvider()
    {
        return [
            [
                '',
                true,
                'Profiler enabled with html output.'
            ],
            [
                '',
                false,
                'Something went wrong while enabling the profiler.'
            ],
            [
                'html',
                true,
                'Profiler enabled with html output.'
            ],
            [
                'html',
                false,
                'Something went wrong while enabling the profiler.'
            ],
            [
                'csvfile',
                true,
                'Profiler enabled with csvfile output. Output will be saved in /var/log/profiler.csv'
            ],
            [
                'csvfile',
                false,
                'Something went wrong while enabling the profiler.'
            ],
            [
                'xml',
                true,
                'Type xml is not one of the built-in output types (html, csvfile). ' .
                'Profiler enabled with xml output.'
            ],
            [
                'xml',
                false,
                'Type xml is not one of the built-in output types (html, csvfile). ' .
                'Something went wrong while enabling the profiler.'
            ],
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

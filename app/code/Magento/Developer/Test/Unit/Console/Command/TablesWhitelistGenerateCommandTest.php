<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\TablesWhitelistGenerateCommand as GenerateCommand;
use Magento\Developer\Model\Setup\Declaration\Schema\WhitelistGenerator;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\ConfigurationMismatchException as ConfigException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TablesWhitelistGenerateCommandTest
 * Test for TablesWhitelistGenerateCommand
 */
class TablesWhitelistGenerateCommandTest extends TestCase
{
    // Exception Messages!
    const CONFIG_EXCEPTION_MESSAGE = 'Configuration Exception Message';
    const EXCEPTION_MESSAGE = 'General Exception Message';

    /** @var WhitelistGenerator|MockObject $whitelistGenerator */
    private $whitelistGenerator;

    /** @var GenerateCommand $instance */
    private $instance;

    protected function setUp(): void
    {
        $this->whitelistGenerator = $this->getMockBuilder(WhitelistGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->instance = new GenerateCommand($this->whitelistGenerator);
    }

    /**
     * Test case for success scenario
     *
     * @param string $arguments
     * @param int $expected
     *
     * @dataProvider successDataProvider
     */
    public function testCommandSuccess(string $arguments, int $expected)
    {
        $this->whitelistGenerator->expects($this->once())
            ->method('generate')
            ->with($arguments);

        $commandTest = $this->execute($arguments);
        $this->assertEquals($expected, $commandTest->getStatusCode());
        $this->assertEquals('', $commandTest->getDisplay());
    }

    /**
     * Test case for failure scenario
     *
     * @param string $arguments
     * @param int $expected
     * @param \Exception|ConfigException $exception
     * @param string $output
     *
     * @dataProvider failureDataProvider
     */
    public function testCommandFailure(string $arguments, int $expected, $exception, string $output)
    {
        $this->whitelistGenerator->expects($this->once())
            ->method('generate')
            ->with($arguments)
            ->willReturnCallback(
                function () use ($exception) {
                    throw $exception;
                }
            );

        $commandTest = $this->execute($arguments);
        $this->assertEquals($expected, $commandTest->getStatusCode());
        $this->assertEquals($output . PHP_EOL, $commandTest->getDisplay());
    }

    /**
     * Data provider for success test case
     *
     * @return array
     */
    public function successDataProvider()
    {
        return [
            [
                'all',
                Cli::RETURN_SUCCESS,

            ],
            [
                'Module_Name',
                Cli::RETURN_SUCCESS
            ]
        ];
    }

    /**
     * Data provider for failure test case
     *
     * @return array
     */
    public function failureDataProvider()
    {
        return [
            [
                'all',
                Cli::RETURN_FAILURE,
                new ConfigException(__('Configuration Exception Message')),
                self::CONFIG_EXCEPTION_MESSAGE
            ],
            [
                'Module_Name',
                Cli::RETURN_FAILURE,
                new ConfigException(__('Configuration Exception Message')),
                self::CONFIG_EXCEPTION_MESSAGE
            ],
            [
                'all',
                Cli::RETURN_FAILURE,
                new \Exception(self::EXCEPTION_MESSAGE),
                self::EXCEPTION_MESSAGE
            ],
            [
                'Module_Name',
                Cli::RETURN_FAILURE,
                new \Exception(self::EXCEPTION_MESSAGE),
                self::EXCEPTION_MESSAGE
            ]
        ];
    }

    /**
     * Execute command test class for symphony
     *
     * @param string $arguments
     *
     * @return CommandTester
     */
    private function execute(string $arguments)
    {
        $commandTest = new CommandTester($this->instance);
        $commandTest->execute(['--' . GenerateCommand::MODULE_NAME_KEY => $arguments]);

        return $commandTest;
    }
}

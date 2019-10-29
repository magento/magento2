<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Console\Command\TablesWhitelistGenerateCommand as GenerateCommand;
use Magento\Developer\Model\Setup\Declaration\Schema\WhitelistGenerator;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\ConfigurationMismatchException as ConfigException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TablesWhitelistGenerateCommandTest
 *
 * @package Magento\Developer\Console\Command
 */
class TablesWhitelistGenerateCommandTest extends TestCase
{
    // Exception Messages!
    const CONFIG_EXCEPTION = 'Configuration Exception';
    const EXCEPTION = 'General Exception';

    /** @var WhitelistGenerator|MockObject $whitelistGenerator */
    private $whitelistGenerator;

    /** @var TablesWhitelistGenerateCommand $instance */
    private $instance;

    protected function setUp()
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
     * @param string $expected
     *
     * @dataProvider successDataProvider
     */
    public function testCommandSuccess(string $arguments, string $expected)
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
     * @param string $expected
     * @param \Exception|ConfigException $exception
     * @param string $output
     *
     * @dataProvider failureDataProvider
     */
    public function testCommandFailure(string $arguments, string $expected, $exception, string $output)
    {
        $this->whitelistGenerator->expects($this->once())
            ->method('generate')
            ->with($arguments)
            ->willReturnCallback(function () use ($exception) {
                throw $exception;
            });

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
                new ConfigException(__(self::CONFIG_EXCEPTION)),
                self::CONFIG_EXCEPTION
            ],
            [
                'Module_Namer',
                Cli::RETURN_FAILURE,
                new ConfigException(__(self::CONFIG_EXCEPTION)),
                self::CONFIG_EXCEPTION
            ],
            [
                'all',
                Cli::RETURN_FAILURE,
                new \Exception(self::EXCEPTION),
                self::EXCEPTION
            ],
            [
                'Module_Name',
                Cli::RETURN_FAILURE,
                new \Exception(self::EXCEPTION),
                self::EXCEPTION
            ]
        ];
    }

    /**
     * Execute command test class for symphony
     *
     * @param string $arguments
     * @return CommandTester
     */
    private function execute(string $arguments)
    {
        $commandTest = new CommandTester($this->instance);
        $commandTest->execute(['--' . GenerateCommand::MODULE_NAME_KEY => $arguments]);

        return $commandTest;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SampleData\Test\Unit\Console\Command;

use Magento\SampleData\Console\Command\SampleDataRemoveCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests for command `sampledata:remove`
 */
class SampleDataRemoveCommandTest extends AbstractSampleDataCommandTest
{
    /**
     * @param array $sampleDataPackages
     * @param int $appRunResult - int 0 if everything went fine, or an error code
     * @param array $composerJsonContent
     * @param string $expectedMsg
     * @return void
     *
     * @dataProvider processDataProvider
     */
    public function testExecute(
        array $sampleDataPackages,
        int $appRunResult,
        array $composerJsonContent,
        string $expectedMsg
    ): void {
        $this->setupMocks(
            $sampleDataPackages,
            '/path/to/composer.json',
            $appRunResult,
            $composerJsonContent
        );
        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * @param array $sampleDataPackages
     * @param int $appRunResult - int 0 if everything went fine, or an error code
     * @param array $composerJsonContent
     * @param string $expectedMsg
     * @return void
     *
     * @dataProvider processDataProvider
     */
    public function testExecuteWithNoUpdate(
        array $sampleDataPackages,
        int $appRunResult,
        array $composerJsonContent,
        string $expectedMsg
    ): void {
        $this->setupMocks(
            $sampleDataPackages,
            '/path/to/composer.json',
            $appRunResult,
            $composerJsonContent,
            ['--no-update' => 1]
        );
        $commandInput = ['--no-update' => 1];

        $commandTester = $this->createCommandTester();
        $commandTester->execute($commandInput);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            'No sample data found in require' => [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 1,
                'composerJsonContent' => [
                    "require" => [
                        "magento/product-community-edition" => "0.0.1",
                    ],
                    "version" => "0.0.1"
                ],
                'expectedMsg' => 'There is an error during remove sample data.' . PHP_EOL,
            ],
            'Successful sample data removing' => [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 0,
                'composerJsonContent' => [
                    "require" => [
                        "magento/product-community-edition" => "0.0.1",
                        "magento/module-cms-sample-data" => "1.0.0-beta",
                    ],
                    "version" => "0.0.1"
                ],
                'expectedMsg' => '',
            ],
        ];
    }

    /**
     * Creates command tester
     *
     * @return CommandTester
     */
    private function createCommandTester(): CommandTester
    {
        return new CommandTester(
            new SampleDataRemoveCommand(
                $this->filesystemMock,
                $this->sampleDataDependencyMock,
                $this->arrayInputFactoryMock,
                $this->applicationFactoryMock
            )
        );
    }

    /**
     * Returns expected arguments for command `composer remove`
     *
     * @param $sampleDataPackages
     * @param $pathToComposerJson
     * @return array
     */
    protected function expectedComposerArgumentsSampleDataCommands(
        array $sampleDataPackages,
        string $pathToComposerJson
    ) : array {
        return [
            'command' => 'remove',
            '--working-dir' => $pathToComposerJson,
            '--no-interaction' => 1,
            '--no-progress' => 1,
            'packages' => array_keys($sampleDataPackages),
        ];
    }
}

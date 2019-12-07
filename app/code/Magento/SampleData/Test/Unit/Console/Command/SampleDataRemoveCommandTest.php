<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Console\Command;

use Magento\SampleData\Console\Command\SampleDataRemoveCommand;
use Symfony\Component\Console\Tester\CommandTester;

class SampleDataRemoveCommandTest extends AbstractSampleDataCommandTest
{

    /**
     * @param array     $sampleDataPackages
     * @param int       $appRunResult - int 0 if everything went fine, or an error code
     * @param string    $expectedMsg
     * @return          void
     *
     * @dataProvider processDataProvider
     */
    public function testExecute(array $sampleDataPackages, $appRunResult, $expectedMsg)
    {
        $this->setupMocks($sampleDataPackages, '/path/to/composer.json', $appRunResult);
        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * @param array     $sampleDataPackages
     * @param int       $appRunResult - int 0 if everything went fine, or an error code
     * @param string    $expectedMsg
     * @return          void
     *
     * @dataProvider processDataProvider
     */
    public function testExecuteWithNoUpdate(array $sampleDataPackages, $appRunResult, $expectedMsg)
    {
        $this->setupMocks(
            $sampleDataPackages,
            '/path/to/composer.json',
            $appRunResult,
            ['--no-update' => 1]
        );
        $commandInput = ['--no-update' => 1];

        $commandTester = $this->createCommandTester();
        $commandTester->execute($commandInput);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'No sample data found' => [
                'sampleDataPackages' => [],
                'appRunResult' => 1,
                'expectedMsg' => 'There is no sample data for current set of modules.' . PHP_EOL,
            ],
            'Successful sample data installation' => [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 0,
                'expectedMsg' => '',
            ],
        ];
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester(): CommandTester
    {
        $commandTester = new CommandTester(
            new SampleDataRemoveCommand(
                $this->filesystemMock,
                $this->sampleDataDependencyMock,
                $this->arrayInputFactoryMock,
                $this->applicationFactoryMock
            )
        );
        return $commandTester;
    }

    /**
     * @param $sampleDataPackages
     * @param $pathToComposerJson
     * @return array
     */
    protected function expectedComposerArguments(
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

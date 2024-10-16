<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SampleData\Test\Unit\Console\Command;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\SampleData\Console\Command\SampleDataDeployCommand;
use Magento\Setup\Model\PackagesAuth;
use Symfony\Component\Console\Tester\CommandTester;

class SampleDataDeployCommandTest extends AbstractSampleDataCommandTestCase
{
    /**
     * Sets mocks for auth file
     *
     * @param bool $authExist True to test with existing auth.json, false without
     * @return void
     */
    protected function setupMocksForAuthFile(bool $authExist): void
    {
        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn($authExist);
        $this->directoryWriteMock->expects($authExist ? $this->never() : $this->once())
            ->method('writeFile')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE, '{}');
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::COMPOSER_HOME)
            ->willReturn($this->directoryWriteMock);
    }

    /**
     * @param array $sampleDataPackages
     * @param int $appRunResult - int 0 if everything went fine, or an error code
     * @param array $composerJsonContent
     * @param string $expectedMsg
     * @param bool $authExist
     * @return void
     *
     * @dataProvider processDataProvider
     */
    public function testExecute(
        array $sampleDataPackages,
        int $appRunResult,
        array $composerJsonContent,
        string $expectedMsg,
        bool $authExist
    ): void {
        $this->setupMocks(
            $sampleDataPackages,
            '/path/to/composer.json',
            $appRunResult,
            $composerJsonContent
        );
        $this->setupMocksForAuthFile($authExist);
        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * @param array $sampleDataPackages
     * @param int $appRunResult - int 0 if everything went fine, or an error code
     * @param array $composerJsonContent
     * @param string $expectedMsg
     * @param bool $authExist
     * @return void
     *
     * @dataProvider processDataProvider
     */
    public function testExecuteWithNoUpdate(
        array $sampleDataPackages,
        int $appRunResult,
        array $composerJsonContent,
        string $expectedMsg,
        bool $authExist
    ): void {
        $this->setupMocks(
            $sampleDataPackages,
            '/path/to/composer.json',
            $appRunResult,
            $composerJsonContent,
            ['--no-update' => 1]
        );
        $this->setupMocksForAuthFile($authExist);
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
    public static function processDataProvider(): array
    {
        return [
            'No sample data found' => [
                'sampleDataPackages' => [],
                'appRunResult' => 1,
                'composerJsonContent' => [
                    'require' => ["magento/product-community-edition" => "0.0.1"],
                ],
                'expectedMsg' => 'There is no sample data for current set of modules.' . PHP_EOL,
                'authExist' => true,
            ],
            'No auth.json found' => [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 1,
                'composerJsonContent' => [
                    'require' => ["magento/product-community-edition" => "0.0.1"],
                ],
                'expectedMsg' => 'There is an error during sample data deployment. Composer file will be reverted.'
                    . PHP_EOL,
                'authExist' => false,
            ],
            'Successful sample data installation' => [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 0,
                'composerJsonContent' => [
                    'require' => ["magento/product-community-edition" => "0.0.1"],
                ],
                'expectedMsg' => 'Sample data modules have been added via composer.'
                    . ' Please run bin/magento setup:upgrade'
                    . PHP_EOL,
                'authExist' => true,
            ],
        ];
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Error in writing Auth file path/to/auth.json. Please check permissions for writing.'
        );

        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn(false);
        $this->directoryWriteMock->expects($this->once())
            ->method('writeFile')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE, '{}')
            ->willThrowException(new Exception('Something went wrong...'));
        $this->directoryWriteMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn('path/to/auth.json');
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::COMPOSER_HOME)
            ->willReturn($this->directoryWriteMock);

        $this->createCommandTester()->execute([]);
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester(): CommandTester
    {
        return new CommandTester(
            new SampleDataDeployCommand(
                $this->filesystemMock,
                $this->sampleDataDependencyMock,
                $this->applicationFactoryMock
            )
        );
    }

    /**
     * @param array $sampleDataPackages
     * @param string $pathToComposerJson
     * @return array
     */
    protected function expectedComposerArgumentsSampleDataCommands(
        array $sampleDataPackages,
        string $pathToComposerJson
    ): array {
        return [
            'command' => 'require',
            '--working-dir' => $pathToComposerJson,
            '--no-progress' => 1,
            'packages' => $this->packageVersionStrings($sampleDataPackages),
        ];
    }

    /**
     * @param array $sampleDataPackages
     * @return array
     */
    private function packageVersionStrings(array $sampleDataPackages): array
    {
        array_walk($sampleDataPackages, static function (&$v, $k) {
            $v = "$k:$v";
        });

        return array_values($sampleDataPackages);
    }
}

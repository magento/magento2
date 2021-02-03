<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Magento\TestFramework\TestCase\SetupTestCase;
use Magento\Framework\Console\Cli;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;

/**
 * The purpose of this test is to verify the declaration:generate:whitelist command.
 */
class TablesWhitelistGenerateCommandTest extends SetupTestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Developer\Console\Command\TablesWhitelistGenerateCommand
     */
    private $command;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * @var TestModuleManager
     */
    private $moduleManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->command = $this->objectManager->create(
            \Magento\Developer\Console\Command\TablesWhitelistGenerateCommand::class
        );
        $this->componentRegistrar = $this->objectManager->create(
            \Magento\Framework\Component\ComponentRegistrar::class
        );
        $this->cliCommand = $this->objectManager->get(CliCommand::class);
        $this->tester = new CommandTester($this->command);
        $this->moduleManager = $this->objectManager->get(TestModuleManager::class);
    }

    /**
     * Execute generate command for whitelist.
     *
     * @moduleName Magento_TestSetupDeclarationModule1
     * @moduleName Magento_TestSetupDeclarationModule8
     * @throws \Exception
     */
    public function testExecute()
    {
        $modules = [
            'Magento_TestSetupDeclarationModule1',
            'Magento_TestSetupDeclarationModule8',
        ];

        $this->cliCommand->install($modules);
        foreach ($modules as $moduleName) {
            $this->moduleManager->updateRevision(
                $moduleName,
                'whitelist_upgrade',
                'db_schema.xml',
                'etc'
            );
        }

        foreach ($modules as $moduleName) {
            $this->checkWhitelistFile($moduleName);
        }
    }

    /**
     * @param string $moduleName
     */
    private function checkWhitelistFile(string $moduleName)
    {
        $modulePath = $this->componentRegistrar->getPath('module', $moduleName);
        $whiteListFileName = $modulePath
            . DIRECTORY_SEPARATOR
            . \Magento\Framework\Module\Dir::MODULE_ETC_DIR
            . DIRECTORY_SEPARATOR
            . \Magento\Framework\Setup\Declaration\Schema\Diff\Diff::GENERATED_WHITELIST_FILE_NAME;

        //run bin/magento declaration:generate:whitelist Magento_TestSetupDeclarationModule1 command.
        $this->tester->execute(['--module-name' => $moduleName], ['interactive' => false]);
        $this->assertSame(Cli::RETURN_SUCCESS, $this->tester->getStatusCode());

        $this->assertFileExists($whiteListFileName);
        $this->assertStringContainsString('', $this->tester->getDisplay());

        $whitelistFileContent = file_get_contents($whiteListFileName);
        $expectedWhitelistContent = file_get_contents(
            dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . implode(
                DIRECTORY_SEPARATOR,
                [
                    '_files',
                    'WhitelistGenerate',
                    str_replace('Magento_', '', $moduleName),
                    'db_schema_whitelist.json'
                ]
            )
        );
        $this->assertEquals($expectedWhitelistContent, $whitelistFileContent);
    }
}

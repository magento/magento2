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
     * {@inheritdoc}
     */
    public function setUp()
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
    }

    /**
     * Execute generate command for whitelist on module Magento_TestSetupDeclarationModule1.
     *
     * @param array $expectedWhitelistContent
     *
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProvider contentsDataProvider
     */
    public function testExecute(array $expectedWhitelistContent)
    {
        $moduleName = 'Magento_TestSetupDeclarationModule1';
        $this->cliCommand->install([$moduleName]);
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
        $this->assertContains('', $this->tester->getDisplay());

        $whitelistContent = json_decode(file_get_contents($whiteListFileName), true);
        $this->assertEquals($expectedWhitelistContent, $whitelistContent);
    }

    /**
     * Data provider for whitelist contents.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function contentsDataProvider(): array
    {
        return [
            [
                'content' => [
                    'reference_table' =>
                        [
                            'column' =>
                                [
                                    'tinyint_ref' => true,
                                    'tinyint_without_padding' => true,
                                    'bigint_without_padding' => true,
                                    'integer_without_padding' => true,
                                    'smallint_with_big_padding' => true,
                                    'smallint_without_default' => true,
                                    'int_without_unsigned' => true,
                                    'int_unsigned' => true,
                                    'bigint_default_nullable' => true,
                                    'bigint_not_default_not_nullable' => true,
                                    'smallint_without_padding' => true,
                                ],
                            'constraint' =>
                                [
                                    'tinyint_primary' => true,
                                ],
                        ],
                    'auto_increment_test' =>
                        [
                            'column' =>
                                [
                                    'int_auto_increment_with_nullable' => true,
                                    'int_disabled_auto_increment' => true,
                                ],
                            'constraint' =>
                                [
                                    'AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE' => true,
                                ],
                        ],
                    'test_table' =>
                        [
                            'column' =>
                                [
                                    'smallint' => true,
                                    'tinyint' => true,
                                    'bigint' => true,
                                    'float' => true,
                                    'double' => true,
                                    'decimal' => true,
                                    'date' => true,
                                    'timestamp' => true,
                                    'datetime' => true,
                                    'longtext' => true,
                                    'mediumtext' => true,
                                    'varchar' => true,
                                    'mediumblob' => true,
                                    'blob' => true,
                                    'boolean' => true,
                                    'varbinary_rename' => true,
                                ],
                            'index' =>
                                [
                                    'TEST_TABLE_TINYINT_BIGINT' => true,
                                ],
                            'constraint' =>
                                [
                                    'TEST_TABLE_SMALLINT_BIGINT' => true,
                                    'TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF' => true,
                                ],
                        ],
                    'store' =>
                        [
                            'column' =>
                                [
                                    'store_owner_id' => true,
                                    'store_owner' => true,
                                ],
                            'constraint' =>
                                [
                                    'STORE_STORE_OWNER_ID_STORE_OWNER_OWNER_ID' => true,
                                ],
                        ],
                    'store_owner' =>
                        [
                            'column' =>
                                [
                                    'owner_id' => true,
                                    'label' => true,
                                ],
                            'constraint' =>
                                [
                                    '' => true,
                                ],
                        ],
                    'some_table' =>
                        [
                            'column' =>
                                [
                                    'some_column' => true,
                                ],
                        ],
                ],
            ],
        ];
    }
}

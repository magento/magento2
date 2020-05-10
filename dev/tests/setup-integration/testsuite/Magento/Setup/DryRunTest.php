<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying declarative installation works.
 */
class DryRunTest extends SetupTestCase
{
    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var CliCommand
     */
    private $cliCommad;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/dry_run_log.php
     */
    public function testDryRunOnCleanDatabase()
    {
        $logFileName = TESTS_TEMP_DIR . '/var/log/' . DryRunLogger::FILE_NAME;
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            ['dry-run' => true]
        );
        self::assertFileExists($logFileName);
        $data = file_get_contents($logFileName);
        self::assertEquals($data, $this->getData()[0]);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/dry_run_log_on_upgrade.php
     */
    public function testDryRunOnUpgrade()
    {
        $logFileName = TESTS_TEMP_DIR . '/var/log/' . DryRunLogger::FILE_NAME;
        $this->cliCommad->install(['Magento_TestSetupDeclarationModule1']);
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'column_modifications',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommad->upgrade(['dry-run' => true]);
        self::assertFileExists($logFileName);
        $data = file_get_contents($logFileName);
        self::assertEquals($data, $this->getData()[0]);
    }
}

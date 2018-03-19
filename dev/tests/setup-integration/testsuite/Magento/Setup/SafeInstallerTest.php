<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\DescribeTable;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying safe declarative installation works.
 */
class SafeInstallerTest extends SetupTestCase
{
    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var CliCommand
     */
    private $cliCommad;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule4
     * @dataProviderFromFile Magento/TestSetupDeclarationModule4/fixture/safe_data_provider.php
     */
    public function testInstallation()
    {
        $testTableData = $this->getData();
        $row = reset($testTableData);
        $this->cliCommad->install(['Magento_TestSetupDeclarationModule4']);
        $adapter = $this->resourceConnection->getConnection();
        $testTableName = $this->resourceConnection->getTableName('test_table');
        $adapter->insertArray(
            $this->resourceConnection->getTableName('test_table'),
            array_keys($row),
            $this->getData()
        );
        //Move new db_schema.xml
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule4',
            'remove_title_column',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommad->upgrade(
            [
                'safe-mode' => true,
            ]
        );
        //Move new db_schema.xml with restored title field
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule4',
            'restore_title_column',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommad->upgrade(
            [
                'data-restore' => true,
            ]
        );
        $testTableSelect = $adapter->select()->from($testTableName);
        self::assertEquals($testTableData, $adapter->fetchAll($testTableSelect));
    }
}

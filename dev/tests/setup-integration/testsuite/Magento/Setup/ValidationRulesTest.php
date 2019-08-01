<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Setup\Declaration\Schema\SchemaConfig;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts.
 */
class ValidationRulesTest extends SetupTestCase
{
    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var SchemaConfig
     */
    private $schemaConfig;

    /**
     * @var CliCommand
     */
    private $cliCommad;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->schemaConfig = $objectManager->create(SchemaConfig::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
    }

    /**
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessageRegExp
     * /Primary key can`t be applied on table "test_table". All columns should be not nullable/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnInvalidPrimaryKey()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'invalid_primary_key',
            'db_schema.xml',
            'etc'
        );

        $this->schemaConfig->getDeclarationConfig();
    }

    /**
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessageRegExp
     * /Column definition "page_id_on" and reference column definition "page_id"
     * are different in tables "dependent" and "test_table"/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnIncosistentReferenceDefinition()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'incosistence_reference_definition',
            'db_schema.xml',
            'etc'
        );
        $this->schemaConfig->getDeclarationConfig();
    }

    /**
     * @expectedException \Magento\Framework\Setup\Exception
     * @expectedExceptionMessageRegExp /Auto Increment column do not have index. Column - "page_id"/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnInvalidAutoIncrementField()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'invalid_auto_increment',
            'db_schema.xml',
            'etc'
        );
        $this->schemaConfig->getDeclarationConfig();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Console\Command\InstallCommand;
use Magento\Setup\Model\Declaration\Schema\ChangeRegistryFactory;
use Magento\Setup\Model\Declaration\Schema\Db\Parser;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\Dto\SchemaFactory;
use Magento\Setup\Model\Declaration\Schema\OperationsExecutor;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;
use \Magento\Setup\Model\Declaration\Schema\Declaration\Parser as DeclarativeParser;

/**
 * The purpose of this test is verifying declarative installation works
 */
class DeclarativeInstallerTest extends SetupTestCase
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
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @var DeclarativeParser
     */
    private $declarativeParser;

    /**
     * @var Parser
     */
    private $generatedParser;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var ChangeRegistryFactory
     */
    private $changeRegistryFactory;

    /**
     * @var OperationsExecutor
     */
    private $operationsExecutor;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->schemaFactory = $objectManager->get(SchemaFactory::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->declarativeParser = $objectManager->get(DeclarativeParser::class);
        $this->generatedParser = $objectManager->get(Parser::class);
        $this->schemaDiff = $objectManager->get(SchemaDiff::class);
        $this->changeRegistryFactory = $objectManager->get(ChangeRegistryFactory::class);
        $this->operationsExecutor = $objectManager->get(OperationsExecutor::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testInstallation()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );
        echo 1;
    }
}

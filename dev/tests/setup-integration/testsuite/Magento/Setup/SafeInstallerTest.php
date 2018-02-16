<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\SchemaConfigInterface;
use Magento\Setup\Model\Declaration\Schema\Sharding;
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
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DescribeTable
     */
    private $describeTable;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->describeTable = $objectManager->get(DescribeTable::class);
        $this->schemaDiff = $objectManager->get(SchemaDiff::class);
        $this->schemaConfig = $objectManager->get(SchemaConfigInterface::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/installation.php
     */
    public function testInstallation()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

    }
}

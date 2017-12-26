<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Console\Command\InstallCommand;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\DescribeTable;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying declarative installation works with different shard
 */
class SplitDbTest extends SetupTestCase
{
    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * @var DescribeTable
     */
    private $describeTable;

    public function setUp()
    {
        $objectManager= Bootstrap::getObjectManager();
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->describeTable = $objectManager->get(DescribeTable::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule2
     * @dataProviderFromFile Magento/TestSetupDeclarationModule2/fixture/shards.php
     */
    public function testSplitDbInstallation()
    {
        $this->cliCommand->install(
            ['Magento_ScalableCheckout', 'Magento_ScalableOms'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $this->cliCommand->splitQuote();
        $this->cliCommand->splitSales();

        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule2'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $default = $this->describeTable->describeShard('default');
        $sales = $this->describeTable->describeShard('sales');
        $checkout = $this->describeTable->describeShard('checkout');
        self::assertEquals(array_replace($default, $sales, $checkout), $this->getData());
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule2
     * @dataProviderFromFile Magento/TestSetupDeclarationModule2/fixture/shards.php
     */
    public function testUpgradeWithSplitDb()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule2', 'Magento_ScalableCheckout', 'Magento_ScalableOms'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $this->cliCommand->splitQuote();
        $this->cliCommand->splitSales();

        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule2'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $default = $this->describeTable->describeShard('default');
        $sales = $this->describeTable->describeShard('sales');
        $checkout = $this->describeTable->describeShard('checkout');
        self::assertEquals(array_replace($default, $sales, $checkout), $this->getData());
    }
}

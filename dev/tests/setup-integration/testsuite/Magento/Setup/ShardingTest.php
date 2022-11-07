<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\DescribeTable;
use Magento\TestFramework\Deploy\ShardingConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;
use Magento\TestFramework\Deploy\TestModuleManager;

/**
 * The purpose of this test is verifying declarative installation works with different shard.
 */
class ShardingTest extends SetupTestCase
{
    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * @var DescribeTable
     */
    private $describeTable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var ShardingConfig
     */
    private $shardingConfig;

    protected function setUp(): void
    {
        $objectManager= Bootstrap::getObjectManager();
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->deploymentConfig = $objectManager->get(DeploymentConfig::class);
        $this->describeTable = $objectManager->get(DescribeTable::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->shardingConfig = $objectManager->get(ShardingConfig::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule2
     * @dataProviderFromFile Magento/TestSetupDeclarationModule2/fixture/shards.php
     */
    public function testInstall()
    {
        $this->shardingConfig->applyConfiguration();
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule2']);
        $this->deploymentConfig->resetData();

        $default = $this->describeTable->describeShard('default');
        $shard1 = $this->describeTable->describeShard('shard_one');
        $shard2 = $this->describeTable->describeShard('shard_two');
        //Check if tables were installed on different shards
        self::assertCount(2, $default);
        self::assertCount(1, $shard1);
        self::assertCount(1, $shard2);
        $this->assertTableCreationStatements($this->getData(), array_replace($default, $shard1, $shard2));
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule2
     * @dataProviderFromFile Magento/TestSetupDeclarationModule2/fixture/shards.php
     */
    public function testUpgrade()
    {
        $this->shardingConfig->applyConfiguration();
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule2']);
        $this->deploymentConfig->resetData();
        $this->cliCommand->upgrade();

        $default = $this->describeTable->describeShard('default');
        $shard1 = $this->describeTable->describeShard('shard_one');
        $shard2 = $this->describeTable->describeShard('shard_two');
        //Check if tables were installed on different shards
        self::assertCount(2, $default);
        self::assertCount(1, $shard1);
        self::assertCount(1, $shard2);
        $this->assertTableCreationStatements($this->getData(), array_replace($default, $shard1, $shard2));
    }

    /**
     * Assert table creation statements
     *
     * @param array $expectedData
     * @param array $actualData
     */
    private function assertTableCreationStatements(array $expectedData, array $actualData): void
    {
        if (!$this->isUsingAuroraDb()) {
            $this->assertEquals($expectedData, $actualData);
        } else {
            ksort($expectedData);
            ksort($actualData);
            $this->assertSameSize($expectedData, $actualData);
            foreach ($expectedData as $key => $value) {
                $this->assertStringContainsString($actualData[$key], $value);
            }
        }
    }
}

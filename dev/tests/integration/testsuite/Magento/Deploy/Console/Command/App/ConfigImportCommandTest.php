<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Flag;
use Magento\Framework\FlagFactory;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigImportCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DeploymentConfig\Reader
     */
    private $reader;

    /**
     * @var DeploymentConfig\Writer
     */
    private $writer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $envConfig;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
        $this->writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->hash = $this->objectManager->get(Hash::class);
        $this->flagFactory = $this->objectManager->get(FlagFactory::class);
        $this->cacheManager = $this->objectManager->get(CacheInterface::class);
        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);

        // Snapshot of configuration.
        $this->config = $this->loadConfig();
        $this->envConfig = $this->loadEnvConfig();
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );

        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);

        $flag = $this->getFlag();
        $flag->getResource()->delete($flag);
        $this->appConfig->reinit();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecuteNothingImport()
    {
        $this->assertEmpty($this->hash->get());

        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([], ['interactive' => false]);

        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Nothing to import.', $commandTester->getDisplay());
        $this->assertEmpty($this->hash->get());
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testImportStores()
    {
        $this->assertEmpty($this->hash->get());

        $dumpCommand = $this->objectManager->create(ApplicationDumpCommand::class);
        $dumpCommandTester = new CommandTester($dumpCommand);
        $dumpCommandTester->execute([]);
        $dumpedData = $this->reader->load(ConfigFilePool::APP_CONFIG);

        $this->writeConfig(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_stores.php'
        );

        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);

        $this->runConfigImportCommand($commandTester);

        /** @var StoreFactory $storeFactory */
        $storeFactory = $this->objectManager->get(StoreFactory::class);
        /** @var WebsiteFactory $websiteFactory */
        $websiteFactory = $this->objectManager->get(WebsiteFactory::class);
        /** @var GroupFactory $groupFactory */
        $groupFactory = $this->objectManager->get(GroupFactory::class);

        $website = $websiteFactory->create()->load('test_website', 'code');
        $this->assertSame($website->getName(), 'Test Website');

        $group = $groupFactory->create();
        $group->getResource()->load($group, 'test_website_store', 'code');
        $this->assertSame($group->getName(), 'Test Website Store');
        $this->assertSame($group->getWebsiteId(), $website->getId());

        $store = $storeFactory->create();
        $store->getResource()->load($store, 'test', 'code');
        $this->assertSame($store->getSortOrder(), '23');
        $this->assertSame($store->getName(), 'Test Store view');
        $this->assertSame($store->getGroupId(), $group->getId());
        $this->assertSame($store->getWebsiteId(), $website->getId());

        $this->writeConfig(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_changed_stores.php'
        );

        $this->runConfigImportCommand($commandTester);

        $store = $storeFactory->create();
        $store->getResource()->load($store, 'test', 'code');
        $this->assertSame($store->getSortOrder(), '23');
        $this->assertSame($store->getName(), 'Changed Test Store view');
        $this->assertSame($store->getGroupId(), $group->getId());
        $this->assertSame($store->getWebsiteId(), $website->getId());

        $website = $websiteFactory->create();
        $website->getResource()->load($website, 'test_website', 'code');
        $this->assertSame($website->getName(), 'Changed Test Website');

        $group = $groupFactory->create();
        $group->getResource()->load($group, 'test_website_store', 'code');
        $this->assertSame($group->getName(), 'Changed Test Website Store');
        $this->assertSame($website->getId(), $group->getWebsiteId());

        $this->writeConfig(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_removed_stores.php'
        );

        $this->runConfigImportCommand($commandTester);

        $group = $groupFactory->create();
        $group->getResource()->load($group, 'test_website_store', 'code');
        $store = $storeFactory->create();
        $store->getResource()->load($store, 'test', 'code');
        $website = $websiteFactory->create();
        $website->getResource()->load($website, 'test_website', 'code');

        $this->assertSame(null, $store->getId());
        $this->assertSame(null, $website->getId());
        $this->assertSame(null, $group->getId());
    }

    /**
     * @magentoDbIsolation disabled
     */
    public function testImportStoresWithWrongConfiguration()
    {
        $this->assertEmpty($this->hash->get());

        $dumpCommand = $this->objectManager->create(ApplicationDumpCommand::class);
        $dumpCommandTester = new CommandTester($dumpCommand);
        $dumpCommandTester->execute([]);
        $dumpedData = $this->reader->load(ConfigFilePool::APP_CONFIG);

        unset($dumpedData['scopes']['websites']['base']);

        $this->writeConfig($dumpedData, []);

        $importCommand = $this->objectManager->create(ConfigImportCommand::class);
        $importCommandTester = new CommandTester($importCommand);
        $importCommandTester->execute([]);

        $this->assertContains(
            'Scopes data should have at least one not admin website, group and store.',
            $importCommandTester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $importCommandTester->getStatusCode());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testImportConfig()
    {
        $correctData = [
            'system' => [
                'default' => [
                    'web' => [
                        'secure' => [
                            'base_url' => 'http://magento2.local/',
                        ],
                    ],
                    'currency' => [
                        'options' => [
                            'base' => 'USD',
                            'default' => 'EUR',
                        ],
                    ],
                ],
            ],
        ];
        $wrongData = [
            'system' => [
                'default' => [
                    'web' => [
                        'secure' => [
                            'base_url' => 'wrong_url',
                        ],
                    ],
                ],
            ],
        ];
        $wrongCurrency = [
            'system' => [
                'default' => [
                    'currency' => [
                        'options' => [
                            'default' => 'GBP',
                        ],
                    ],
                ],
            ],
        ];

        $this->writeConfig($this->config, $correctData);

        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([], ['interactive' => false]);

        $this->assertContains('System config was processed', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());

        $this->writeConfig(
            $this->config,
            $wrongData
        );

        $commandTester->execute([]);

        $this->assertContains(
            'Invalid Secure Base URL. Value must be a URL or one of placeholders: {{base_url}},{{unsecure_base_url}}',
            $commandTester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $commandTester->getStatusCode());

        $this->writeConfig($this->config, $wrongCurrency);

        $commandTester->execute([]);

        $this->assertContains(
            'Import failed: Sorry, the default display currency you selected is not available in allowed currencies.',
            $commandTester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $commandTester->getStatusCode());

        $this->writeConfig(
            $this->config,
            $correctData
        );

        $commandTester->execute([]);

        $this->assertContains('Nothing to import', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * Saves new data.
     *
     * @param array $originalData
     * @param array $newData
     */
    public function writeConfig(array $originalData, array $newData)
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );

        $newData = array_replace_recursive(
            $originalData,
            $newData
        );
        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $newData], true);
    }

    /**
     * @return Flag
     */
    private function getFlag()
    {
        $flag = $this->flagFactory->create();
        $flag->getResource()->load($flag, Hash::CONFIG_KEY, 'flag_code');

        return $flag;
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_CONFIG);
    }

    /**
     * @return array
     */
    private function loadEnvConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * Runs ConfigImportCommand and asserts that command run successfully
     *
     * @param $commandTester
     */
    private function runConfigImportCommand($commandTester)
    {
        $this->appConfig->reinit();
        $commandTester->execute([], ['interactive' => false]);

        $this->assertContains(
            'Processing configurations data from configuration file...',
            $commandTester->getDisplay()
        );
        $this->assertContains('Stores were processed', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
    }
}

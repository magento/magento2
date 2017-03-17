<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
use Symfony\Component\Console\Application;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
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
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecuteNothingImport()
    {
        $this->markTestIncomplete('Not complete');

        $this->assertEmpty($this->hash->get());
        $application = $this->objectManager->create(Application::class);
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-n' => true]);
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

        $this->saveConfigFromDumpedData(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_stores.php'
        );

        $application = $this->objectManager->create(Application::class);
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['-n' => true]);

        $this->assertContains(
            'Processing configurations data from configuration file...',
            $commandTester->getDisplay()
        );
        $this->assertContains('Stores were processed', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());

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

        $this->saveConfigFromDumpedData(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_changed_stores.php'
        );

        $commandTester->execute(['-n' => true]);

        $this->assertContains(
            'Processing configurations data from configuration file...',
            $commandTester->getDisplay()
        );
        $this->assertContains('Stores were processed', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());

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

        $this->saveConfigFromDumpedData(
            $dumpedData,
            require __DIR__ . '/../../../_files/scopes/config_with_removed_stores.php'
        );

        $commandTester->execute(['-n' => true]);

        $this->assertContains(
            'Processing configurations data from configuration file...',
            $commandTester->getDisplay()
        );
        $this->assertContains('Stores were processed', $commandTester->getDisplay());
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());

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
     * Saves new data.
     *
     * @param array $dumpedData
     * @param array $mergeData
     */
    public function saveConfigFromDumpedData(array $dumpedData, array $mergeData)
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );

        $mergeData = array_replace_recursive(
            $dumpedData,
            $mergeData
        );
        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $mergeData], true);
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
}

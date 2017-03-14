<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
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
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecuteNothingImport()
    {
        $this->writer->saveConfig([
            ConfigFilePool::APP_CONFIG => []
        ]);
        $this->assertEmpty($this->hash->get());
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--' . ConfigImportCommand::INPUT_OPTION_FORCE => true
        ]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Nothing to import.', $commandTester->getDisplay());
        $this->assertEmpty($this->hash->get());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testImportStores()
    {
        $this->assertEmpty($this->hash->get());
        $this->writer->saveConfig([
            ConfigFilePool::APP_CONFIG => require __DIR__ . '/../../../_files/scopes/config_with_stores.php'
        ]);
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--' . ConfigImportCommand::INPUT_OPTION_FORCE => true
        ]);

        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains('Stores were processed', $commandTester->getDisplay());

        /** @var StoreFactory $storeFactory */
        $storeFactory = $this->objectManager->get(StoreFactory::class);
        /** @var WebsiteFactory $websiteFactory */
        $websiteFactory = $this->objectManager->get(WebsiteFactory::class);
        /** @var GroupFactory $groupFactory */
        $groupFactory = $this->objectManager->get(GroupFactory::class);

        $website = $websiteFactory->create()->load('test', 'code');
        $this->assertSame($website->getName(), 'Test Website');

        $group = $groupFactory->create()->load('test_website_store', 'code');
        $this->assertSame($group->getName(), 'Test Website Store');
        $this->assertSame($group->getWebsiteId(), $website->getId());

        $store = $storeFactory->create()->load('test', 'code');
        $this->assertSame($store->getSortOrder(), '23');
        $this->assertSame($store->getName(), 'Test Store view');
        $this->assertSame($store->getGroupId(), $group->getId());
        $this->assertSame($store->getWebsiteId(), $website->getId());

        $this->writer->saveConfig([
            ConfigFilePool::APP_CONFIG => require __DIR__ . '/../../../_files/scopes/config_with_changed_stores.php'
        ]);

        $commandTester->execute([
            '--' . ConfigImportCommand::INPUT_OPTION_FORCE => true
        ]);

        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains('Stores were processed', $commandTester->getDisplay());

        $store = $storeFactory->create()->load('test', 'code');
        $this->assertSame($store->getSortOrder(), '23');
        $this->assertSame($store->getName(), 'Changed Test Store view');
        $this->assertSame($store->getGroupId(), $group->getId());
        $this->assertSame($store->getWebsiteId(), $website->getId());

        $website = $websiteFactory->create()->load('test', 'code');
        $this->assertSame($website->getName(), 'Changed Main Test');

        $group = $groupFactory->create()->load('test_website_store', 'code');
        $this->assertSame($group->getName(), 'Changed Test Website Store');
        $this->assertSame($website->getId(), $group->getWebsiteId());

        $this->writer->saveConfig(
            [ConfigFilePool::APP_CONFIG => require __DIR__ . '/../../../_files/scopes/config_with_removed_stores.php'],
            true
        );

        $commandTester->execute([
            '--' . ConfigImportCommand::INPUT_OPTION_FORCE => true
        ]);

        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains('Stores were processed', $commandTester->getDisplay());

        $group = $groupFactory->create()->load('test_website_store', 'code');
        $store = $storeFactory->create()->load('test', 'code');
        $website = $websiteFactory->create()->load('test', 'code');

        $this->assertSame(null, $store->getId());
        $this->assertSame(null, $website->getId());
        $this->assertSame(null, $group->getId());
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

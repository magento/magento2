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

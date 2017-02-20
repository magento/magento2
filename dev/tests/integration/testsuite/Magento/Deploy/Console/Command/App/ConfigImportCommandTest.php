<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\DeploymentConfig\ConfigImporterPool;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Deploy\Console\Command\App\ConfigImportCommand\IntegrationTestImporter;
use Magento\Framework\App\DeploymentConfig\ConfigHashManager;

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
     * @var array
     */
    private $contentEnvFile = [];

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure([
            ConfigImporterPool::class => [
                'arguments' => [
                    'importers' => [
                        'integrationTestImporter' => IntegrationTestImporter::class
                    ]
                ]
            ]
        ]);
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
        $this->writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);

        $this->contentEnvFile = $this->getEnvFileContent();
    }

    public function tearDown()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->delete(
            $this->getFileName()
        );
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->contentEnvFile]);
    }

    public function testExecuteNothingImport()
    {
        $this->assertArrayNotHasKey(ConfigHashManager::CONFIG_KEY, $this->contentEnvFile);
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains('Nothing to import', $commandTester->getDisplay());
        $this->assertArrayNotHasKey(ConfigHashManager::CONFIG_KEY, $this->getEnvFileContent());
    }

    public function testExecuteWithImport()
    {
        $this->assertArrayNotHasKey(ConfigHashManager::CONFIG_KEY, $this->contentEnvFile);
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->getFileName(),
            file_get_contents(__DIR__ . '/../../../_files/_config.local.php')
        );
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains('Integration test data is imported!', $commandTester->getDisplay());
        $this->assertArrayHasKey(ConfigHashManager::CONFIG_KEY, $this->getEnvFileContent());
    }

    /**
     * @return array
     */
    private function getEnvFileContent()
    {
        return $this->reader->loadConfigFile(
            ConfigFilePool::APP_ENV,
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            true
        );
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        /** @var ConfigFilePool $configFilePool */
        $configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $filePool = $configFilePool->getInitialFilePools();

        return $filePool[ConfigFilePool::LOCAL][ConfigFilePool::APP_CONFIG];
    }
}

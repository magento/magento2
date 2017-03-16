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
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Deploy\Console\Command\App\ConfigImportCommand\IntegrationTestImporter;
use Magento\Deploy\Console\Command\App\ConfigImportCommand\IntegrationTestSecondImporter;
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

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure([
            ImporterPool::class => [
                'arguments' => [
                    'importers' => [
                        'integrationTestImporter' => [
                            'class' => IntegrationTestImporter::class,
                            'sortOrder' => 20
                        ],
                        'integrationTestSecondImporter' => [
                            'class' => IntegrationTestSecondImporter::class,
                        ]
                    ]
                ]
            ]
        ]);
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
        $this->writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->hash = $this->objectManager->get(Hash::class);

        $this->config = $this->loadConfig();
    }

    public function tearDown()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        /** @var DeploymentConfig\Writer $writer */
        $writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecuteNothingImport()
    {
        $this->assertEmpty($this->hash->get());
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Nothing to import.', $commandTester->getDisplay());
        $this->assertEmpty($this->hash->get());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecuteWithImport()
    {
        $this->assertEmpty($this->hash->get());
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            file_get_contents(__DIR__ . '/../../../_files/config.php')
        );
        $command = $this->objectManager->create(ConfigImportCommand::class);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertContains('Start import', $commandTester->getDisplay());
        $this->assertContains(
            "Integration second test data is imported!\nIntegration test data is imported!",
            $commandTester->getDisplay()
        );
        $this->assertArrayHasKey('integrationTestImporter', $this->hash->get());
        $this->assertArrayHasKey('integrationTestSecondImporter', $this->hash->get());
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_CONFIG);
    }
}

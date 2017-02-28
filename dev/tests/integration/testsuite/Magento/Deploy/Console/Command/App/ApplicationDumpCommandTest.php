<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplicationDumpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DeploymentConfig\FileReader
     */
    private $reader;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DeploymentConfig\Writer
     */
    private $writer;

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
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(DeploymentConfig\FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
        $this->writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);

        // Snapshot of configuration.
        $this->config = $this->loadConfig();
        $this->envConfig = $this->loadEnvConfig();
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
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Deploy/_files/config_data.php
     */
    public function testExecute()
    {
        $this->assertArrayNotHasKey(Hash::CONFIG_KEY, $this->envConfig);
        $this->objectManager->configure([
            \Magento\Config\Model\Config\Export\ExcludeList::class => [
                'arguments' => [
                    'configs' => [
                        'web/test/test_value_1' => '',
                        'web/test/test_value_2' => '',
                        'web/test/test_sensitive' => '1',
                    ],
                ],
            ],
        ]);

        $comment = 'The configuration file doesn\'t contain sensitive data for security reasons. '
            . 'Sensitive data can be stored in the following environment variables:'
            . "\nCONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE for web/test/test_sensitive";
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->at(0))
            ->method('writeln')
            ->with(['system' => $comment]);
        $outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>Done.</info>');

        /** @var ApplicationDumpCommand command */
        $command = $this->objectManager->create(ApplicationDumpCommand::class);
        $command->run($this->getMock(InputInterface::class), $outputMock);

        $config = $this->loadConfig();

        $this->validateSystemSection($config);
        $this->validateThemesSection($config);
        $this->assertArrayHasKey(Hash::CONFIG_KEY, $this->loadEnvConfig());
    }

    /**
     * Validates 'system' section in configuration data.
     *
     * @param array $config The configuration array
     * @return void
     */
    private function validateSystemSection(array $config)
    {
        $this->assertArrayHasKey(
            'test_value_1',
            $config['system']['default']['web']['test']
        );
        $this->assertArrayHasKey(
            'test_value_2',
            $config['system']['default']['web']['test']
        );
        $this->assertArrayNotHasKey(
            'test_sensitive',
            $config['system']['default']['web']['test']
        );
    }

    /**
     * Validates 'themes' section in configuration data.
     *
     * @param array $config The configuration array
     * @return void
     */
    private function validateThemesSection(array $config)
    {
        $this->assertEquals(
            [
                'parent_id' => null,
                'theme_path' => 'Magento/backend',
                'theme_title' => 'Magento 2 backend',
                'is_featured' => '0',
                'area' => 'adminhtml',
                'type' => '0',
                'code' => 'Magento/backend',
            ],
            $config['themes']['Magento/backend']
        );
        $this->assertEquals(
            [
                'parent_id' => null,
                'theme_path' => 'Magento/blank',
                'theme_title' => 'Magento Blank',
                'is_featured' => '0',
                'area' => 'frontend',
                'type' => '0',
                'code' => 'Magento/blank',
            ],
            $config['themes']['Magento/blank']
        );
        $this->assertEquals(
            [
                'parent_id' => 'Magento/blank',
                'theme_path' => 'Magento/luma',
                'theme_title' => 'Magento Luma',
                'is_featured' => '0',
                'area' => 'frontend',
                'type' => '0',
                'code' => 'Magento/luma',
            ],
            $config['themes']['Magento/luma']
        );
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
        /** @var DeploymentConfig\Writer $writer */
        $writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);

        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $deploymentConfig->resetData();

        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);
    }
}

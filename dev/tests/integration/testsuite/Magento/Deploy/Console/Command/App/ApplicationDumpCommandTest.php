<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Config\Model\Config\TypePool;
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
class ApplicationDumpCommandTest extends \PHPUnit\Framework\TestCase
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
    private $envConfig;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(DeploymentConfig\FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
        $this->writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->hash = $this->objectManager->get(Hash::class);

        // Snapshot of configuration.
        $this->config = $this->loadConfig();
        $this->envConfig = $this->loadEnvConfig();

        $this->writer->saveConfig(
            [
                ConfigFilePool::APP_CONFIG => [
                    'system' => [
                        'default' => [
                            'web' => [
                                'test' => [
                                    'test_value_3' => 'value from the file'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            true
        );
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
     * @return string
     */
    private function loadRawConfig()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::CONFIG)
            ->readFile($this->configFilePool->getPath(ConfigFilePool::APP_CONFIG));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Deploy/_files/config_data.php
     */
    public function testExecute()
    {
        $this->objectManager->configure([
            ExcludeList::class => [
                'arguments' => [
                    'configs' => [
                        'web/test/test_value_1' => '',
                        'web/test/test_value_2' => '0',
                        'web/test/test_sensitive' => '1',
                    ],
                ],
            ],
            TypePool::class => [
                'arguments' => [
                    'sensitive' => [
                        'web/test/test_sensitive1' => '',
                        'web/test/test_sensitive2' => '0',
                        'web/test/test_sensitive3' => '1',
                        'web/test/test_sensitive_environment4' => '1',
                        'web/test/test_sensitive_environment5' => '1',
                        'web/test/test_sensitive_environment6' => '0',
                    ],
                    'environment' => [
                        'web/test/test_sensitive_environment4' => '1',
                        'web/test/test_sensitive_environment5' => '0',
                        'web/test/test_sensitive_environment6' => '1',
                        'web/test/test_environment7' => '',
                        'web/test/test_environment8' => '0',
                        'web/test/test_environment9' => '1',
                    ],
                ]
            ]
        ]);

        $comment = implode(PHP_EOL, [
            'Shared configuration was written to config.php and system-specific configuration to env.php.',
            'Shared configuration file (config.php) doesn\'t contain sensitive data for security reasons.',
            'Sensitive data can be stored in the following environment variables:',
            'CONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE for web/test/test_sensitive',
            'CONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE3 for web/test/test_sensitive3',
            'CONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE_ENVIRONMENT4 for web/test/test_sensitive_environment4',
            'CONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE_ENVIRONMENT5 for web/test/test_sensitive_environment5'
        ]);
        $outputMock = $this->getMockForAbstractClass(OutputInterface::class);
        $outputMock->expects($this->at(0))
            ->method('writeln')
            ->with(['system' => $comment]);
        $outputMock->expects($this->at(1))
            ->method('writeln')
            ->with($this->matchesRegularExpression('/<info>Done. Config types dumped: [a-z0-9,\s]+<\/info>/'));

        /** @var ApplicationDumpCommand command */
        $command = $this->objectManager->create(ApplicationDumpCommand::class);
        $command->run($this->getMockForAbstractClass(InputInterface::class), $outputMock);

        $config = $this->loadConfig();

        $this->validateSystemSection($config);
        $this->validateThemesSection($config);

        $configEnv = $this->loadEnvConfig();
        $this->validateSystemEnvSection($configEnv);

        $this->assertNotEmpty($this->hash->get());
        $this->assertStringContainsString('For the section: system', $this->loadRawConfig());
    }

    /**
     * Validates 'system' section in configuration data.
     *
     * @param array $config The configuration array
     * @return void
     */
    private function validateSystemSection(array $config)
    {
        $this->assertArrayHasKey('test_value_1', $config['system']['default']['web']['test']);
        $this->assertArrayHasKey('test_value_2', $config['system']['default']['web']['test']);
        $this->assertArrayHasKey('test_sensitive1', $config['system']['default']['web']['test']);
        $this->assertArrayHasKey('test_sensitive2', $config['system']['default']['web']['test']);
        $this->assertArrayHasKey('test_environment7', $config['system']['default']['web']['test']);
        $this->assertArrayHasKey('test_environment8', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_sensitive', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_sensitive3', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_sensitive_environment4', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_sensitive_environment5', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_sensitive_environment6', $config['system']['default']['web']['test']);
        $this->assertArrayNotHasKey('test_environment9', $config['system']['default']['web']['test']);
        /** @see Magento/Deploy/_files/config_data.php */
        $this->assertEquals(
            'frontend/Magento/blank',
            $config['system']['default']['design']['theme']['theme_id']
        );
        $this->assertEquals(
            'frontend/Magento/luma',
            $config['system']['stores']['default']['design']['theme']['theme_id']
        );
        $this->assertEquals(
            'frontend/Magento/luma',
            $config['system']['websites']['base']['design']['theme']['theme_id']
        );

        $this->assertEquals('value from the file', $config['system']['default']['web']['test']['test_value_3']);
        $this->assertEquals('GB', $config['system']['default']['general']['country']['default']);
        $this->assertEquals(
            'HK,IE,MO,PA,GB',
            $config['system']['default']['general']['country']['optional_zip_countries']
        );
    }

    /**
     * Validates 'system' section in environment configuration data.
     *
     * @param array $config The configuration array
     * @return void
     */
    private function validateSystemEnvSection(array $config)
    {
        $envTestKeys = [
            'test_sensitive',
            'test_sensitive3',
            'test_sensitive_environment4',
            'test_sensitive_environment5',
            'test_sensitive_environment6',
            'test_environment9'
        ];

        $this->assertEmpty(
            array_diff($envTestKeys, array_keys($config['system']['default']['web']['test']))
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
            $config['themes']['adminhtml/Magento/backend']
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
            $config['themes']['frontend/Magento/blank']
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
            $config['themes']['frontend/Magento/luma']
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );

        /** @var DeploymentConfig\Writer $writer */
        $writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);

        /** @var DeploymentConfig\Writer $writer */
        $writer = $this->objectManager->get(DeploymentConfig\Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);

        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $deploymentConfig->resetData();
    }
}

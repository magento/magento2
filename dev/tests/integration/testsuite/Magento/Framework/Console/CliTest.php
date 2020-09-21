<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Console;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CliTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var array
     */
    private $envConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->writer = $this->objectManager->get(Writer::class);

        $this->envConfig = $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );

        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig], true);
    }

    /**
     * Checks that settings from env.php config file are applied
     * to created application instance.
     *
     * @magentoAppIsolation enabled
     * @param bool $isPub
     * @param array $params
     * @dataProvider documentRootIsPubProvider
     */
    public function testDocumentRootIsPublic($isPub, $params)
    {
        $config = include __DIR__ . '/_files/env.php';
        $config['directories']['document_root_is_pub'] = $isPub;
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $config], true);

        $cli = new Cli();
        $cliReflection = new \ReflectionClass($cli);

        $serviceManagerProperty = $cliReflection->getProperty('serviceManager');
        $serviceManagerProperty->setAccessible(true);
        $serviceManager = $serviceManagerProperty->getValue($cli);
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(DeploymentConfig::class, $deploymentConfig);
        $serviceManagerProperty->setAccessible(false);

        $documentRootResolver = $cliReflection->getMethod('documentRootResolver');
        $documentRootResolver->setAccessible(true);

        self::assertEquals($params, $documentRootResolver->invoke($cli));
    }

    /**
     * Provides document root setting and expecting
     * properties for object manager creation.
     *
     * @return array
     */
    public function documentRootIsPubProvider(): array
    {
        return [
            [true, [
                'MAGE_DIRS' => [
                    'pub' => ['uri' => ''],
                    'media' => ['uri' => 'media'],
                    'static' => ['uri' => 'static'],
                    'upload' => ['uri' => 'media/upload']
                ]
            ]],
            [false, []]
        ];
    }
}

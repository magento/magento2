<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\MessageQueue\Config;
use Magento\Framework\MessageQueue\Config\Data;
use Magento\Framework\MessageQueue\Config\Reader\Xml;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ConfigGetConsumersTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var Config
     */
    private $configSubject;

    /**
     * @var FileReader
     */
    private $fileReader;
    /**
     * @var array
     */
    private $envConfigBackup;

    /**
     * @var Writer
     */
    private $fileWriter;

    /**
     * @var Xml
     */
    private $xmlReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileWriter = $this->objectManager->get(Writer::class);
        $this->xmlReader = $this->objectManager->create(Xml::class);
        $this->fileReader = $this->objectManager->get(FileReader::class);

        $this->envConfigBackup = $this->fileReader->load(ConfigFilePool::APP_ENV);
        $customEnvConfig = $this->buildCustomEnvConfigWithConsumers();
        $this->fileWriter->saveConfig([ConfigFilePool::APP_ENV => $customEnvConfig]);

        /** @var Data data */
        $configData = $this->objectManager->create(
            Data::class,
            [
                'cacheId' => uniqid(microtime())
            ]
        );

        $this->configSubject = $this->objectManager->create(
            Config::class,
            [
                'queueConfigData' => $configData
            ]
        );
    }

    public function testGetConsumers(): void
    {
        $consumers = $this->configSubject->getConsumers();

        foreach ($consumers as $consumer) {
            $this->assertIsString($consumer['name']);
            $this->assertIsArray($consumer['handlers']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $filesystem = $this->objectManager->get(Filesystem::class);
        $configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->fileWriter->saveConfig([ConfigFilePool::APP_ENV => $this->envConfigBackup]);
    }

    private function buildCustomEnvConfigWithConsumers(): array
    {
        $data = $this->xmlReader->read();
        $names = array_keys($data['consumers']);
        $consumers = [];
        foreach ($names as $name) {
            $consumers[$name] = ['connection' => 'amqp'];
        }

        return [
            'queue' => [
                'amqp' => [
                    'host' => 'localhost',
                    'port' => '5672',
                    'user' => 'guest',
                    'password' => 'guest',
                    'virtualhost' => '/',
                    'ssl' => ''
                ],
                'consumers' => $consumers
            ],
        ];
    }
}

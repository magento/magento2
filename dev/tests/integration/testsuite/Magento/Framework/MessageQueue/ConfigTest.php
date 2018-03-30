<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Test of communication configuration reading and parsing.
 *
 * @magentoCache config disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetConsumers()
    {
        $consumers = $this->getConfigData()->getConsumers();
        $expectedParsedConfig = include __DIR__ . '/_files/valid_expected_queue.php';
        $this->assertEquals($expectedParsedConfig['consumers'], $consumers);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetPublishers()
    {
        $publishers = $this->getConfigData()->getPublishers();
        $expectedParsedConfig = include __DIR__ . '/_files/valid_expected_queue.php';
        $this->assertEquals($expectedParsedConfig['publishers'], $publishers);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetBinds()
    {
        $binds = $this->getConfigData()->getBinds();
        $expectedParsedConfig = include __DIR__ . '/_files/valid_expected_queue.php';
        $this->assertEquals($expectedParsedConfig['binds'], $binds);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetMaps()
    {
        $topicName = 'topic.broker.test';
        $queue = $this->getConfigData()->getQueuesByTopic($topicName);
        $expectedParsedConfig = include __DIR__ . '/_files/valid_expected_queue.php';
        $this->assertEquals(
            $expectedParsedConfig['exchange_topic_to_queues_map']['magento--topic.broker.test'],
            $queue
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetTopic()
    {
        $topicName = 'topic.broker.test';
        $topic = $this->getConfigData()->getTopic($topicName);
        $expectedParsedConfig = include __DIR__ . '/_files/valid_expected_queue.php';
        $this->assertEquals($expectedParsedConfig['topics'][$topicName], $topic);
    }

    /**
     * Return mocked config data
     *
     * @return \Magento\Framework\MessageQueue\ConfigInterface
     */
    private function getConfigData()
    {
        return $this->getConfigInstance(
            [
                __DIR__ . '/_files/valid_new_queue.xml'
            ]
        );
    }

    /**
     * Create config instance initialized with configuration from $configFilePath
     *
     * @param string|string[] $configFilePath
     * @param string|null $envConfigFilePath
     * @return \Magento\Framework\MessageQueue\ConfigInterface
     */
    protected function getConfigInstance($configFilePath, $envConfigFilePath = null)
    {
        $content = [];
        if (is_array($configFilePath)) {
            foreach ($configFilePath as $file) {
                $content[] = file_get_contents($file);
            }
        } else {
            $content[] = file_get_contents($configFilePath);
        }
        $fileResolver = $this->getMockForAbstractClass(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolver->expects($this->any())
            ->method('get')
            ->willReturn($content);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $topicConverter = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig::class,
            [
                'communicationConfig' => $this->getCommunicationConfigInstance()
            ]
        );

        $converter = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Reader\Xml\CompositeConverter::class,
            [
                'converters' => [
                    ['converter' => $topicConverter, 'sortOrder' => 10]
                ]
            ]
        );
        $xmlReader = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Reader\Xml::class,
            [
                'fileResolver' => $fileResolver,
                'converter' => $converter,
            ]
        );
        $deploymentConfigReader = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig\Reader::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $envConfigData = include $envConfigFilePath ?: __DIR__ . '/_files/valid_queue_input.php';
        $deploymentConfigReader->expects($this->any())->method('load')->willReturn($envConfigData);
        $deploymentConfig = $objectManager->create(
            \Magento\Framework\App\DeploymentConfig::class,
            ['reader' => $deploymentConfigReader]
        );
        $envReader = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Reader\Env::class,
            [
                'deploymentConfig' => $deploymentConfig
            ]
        );
        $methodsMap = $objectManager->create(\Magento\Framework\Reflection\MethodsMap::class);
        $envValidator = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Reader\Env\Validator::class,
            [
                'methodsMap' => $methodsMap
            ]
        );

        $compositeReader = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\CompositeReader::class,
            [
                'readers' => [
                    ['reader' => $xmlReader, 'sortOrder' => 10],
                    ['reader' => $envReader, 'sortOrder' => 20]
                ],
            ]
        );

        /** @var \Magento\Framework\MessageQueue\Config $configData */
        $configData = $objectManager->create(
            \Magento\Framework\MessageQueue\Config\Data::class,
            [
                'reader' => $compositeReader,
                'envValidator' => $envValidator
            ]
        );
        return $objectManager->create(
            \Magento\Framework\MessageQueue\Config::class,
            ['queueConfigData' => $configData]
        );
    }

    /**
     * Get mocked Communication Config Instance
     *
     * @return \Magento\Framework\Communication\ConfigInterface
     */
    private function getCommunicationConfigInstance()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fileResolver = $this->getMockForAbstractClass(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolver->expects($this->any())
            ->method('get')
            ->willReturn([file_get_contents(__DIR__ . '/_files/communication.xml')]);

        $xmlReader = $objectManager->create(
            \Magento\Framework\Communication\Config\Reader\XmlReader::class,
            [
                'fileResolver' => $fileResolver,
            ]
        );

        $compositeReader = $objectManager->create(
            \Magento\Framework\Communication\Config\CompositeReader::class,
            [
                'readers' => [
                    ['reader' => $xmlReader, 'sortOrder' => 10],
                    [
                        'reader' => $objectManager->create(
                            \Magento\Framework\Communication\Config\Reader\EnvReader::class
                        ),
                        'sortOrder' => 20
                    ]
                ],
            ]
        );

        /** @var \Magento\Framework\Communication\Config $configData */
        $configData = $objectManager->create(
            \Magento\Framework\Communication\Config\Data::class,
            [
                'reader' => $compositeReader
            ]
        );

        $config = $objectManager->create(
            \Magento\Framework\Communication\ConfigInterface::class,
            [
                'configData' => $configData
            ]
        );
        return $config;
    }
}

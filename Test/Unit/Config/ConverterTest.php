<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Config;

use Magento\Framework\Amqp\Config\Converter;

/**
 * @codingStandardsIgnoreFile
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Amqp\Config\Converter
     */
    private $converter;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->getMock();

        $this->converter = $objectManager->getObject(
            'Magento\Framework\Amqp\Config\Converter',
            ['deploymentConfig' => $this->deploymentConfigMock]
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $expected = $this->getConvertedQueueConfig();
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test converting valid configuration with publisher for topic overridden in env.php
     */
    public function testConvertWithTopicsEnvOverride()
    {
        $customizedTopic = 'customer.deleted';
        $customPublisher = 'test-publisher-1';
        $envTopicsConfig = [
            'topics' => [
                'some_topic_name' => 'custom_publisher',
                $customizedTopic => $customPublisher,
            ]
        ];
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Converter::ENV_QUEUE)
            ->willReturn($envTopicsConfig);
        $expected = $this->getConvertedQueueConfig();
        $expected[Converter::TOPICS][$customizedTopic][Converter::TOPIC_PUBLISHER] = $customPublisher;
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test converting valid configuration with invalid override configuration in env.php
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Publisher "invalid_publisher_name", specified in env.php for topic "customer.deleted" i
     */
    public function testConvertWithTopicsEnvOverrideException()
    {
        $customizedTopic = 'customer.deleted';
        $envTopicsConfig = [
            'topics' => [
                'some_topic_name' => 'custom_publisher',
                $customizedTopic => 'invalid_publisher_name',
            ]
        ];
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Converter::ENV_QUEUE)
            ->willReturn($envTopicsConfig);
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $this->converter->convert($dom);
    }

    /**
     * Test converting valid configuration with connection for consumer overridden in env.php
     */
    public function testConvertWithConsumersEnvOverride()
    {
        $customizedConsumer = 'customerDeletedListener';
        $customConnection = 'test-queue-3';
        $customMaxMessages = 5255;
        $envConsumersConfig = [
            'consumers' => [
                $customizedConsumer => ['connection' => $customConnection, 'max_messages' => $customMaxMessages],
            ]
        ];
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Converter::ENV_QUEUE)
            ->willReturn($envConsumersConfig);
        $expected = $this->getConvertedQueueConfig();
        $expected[Converter::CONSUMERS][$customizedConsumer][Converter::CONSUMER_CONNECTION] = $customConnection;
        $expected[Converter::CONSUMERS][$customizedConsumer][Converter::CONSUMER_MAX_MESSAGES] = $customMaxMessages;
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Get content of _files/queue.xml converted into array.
     *
     * @return array
     * 
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getConvertedQueueConfig()
    {
        return [
            'publishers' => [
                'test-publisher-1' => [
                    'name' => 'test-publisher-1',
                    'connection' => 'rabbitmq',
                    'exchange' => 'magento',
                ],
                'test-publisher-2' => [
                    'name' => 'test-publisher-2',
                    'connection' => 'db',
                    'exchange' => 'magento',
                ],
                'test-publisher-3' => [
                    'name' => 'test-publisher-3',
                    'connection' => 'rabbitmq',
                    'exchange' => 'test-exchange-1',
                ],
            ],
            'topics' => [
                'customer.created' => [
                    'name' => 'customer.created',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-1',
                ],
                'customer.created.one' => [
                    'name' => 'customer.created.one',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-1',
                ],
                'customer.created.one.two' => [
                    'name' => 'customer.created.one.two',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-1',
                ],
                'customer.created.two' => [
                    'name' => 'customer.created.two',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-1',
                ],
                'customer.updated' => [
                    'name' => 'customer.updated',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-2',
                ],
                'customer.deleted' => [
                    'name' => 'customer.deleted',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-publisher-2',
                ],
                'cart.created' => [
                    'name' => 'cart.created',
                    'schema' => 'Magento\\Quote\\Api\\Data\\CartInterface',
                    'publisher' => 'test-publisher-3',
                ],
                'cart.created.one' => [
                    'name' => 'cart.created.one',
                    'schema' => 'Magento\\Quote\\Api\\Data\\CartInterface',
                    'publisher' => 'test-publisher-3',
                ],
            ],
            'consumers' => [
                'customerCreatedListener' => [
                    'name' => 'customerCreatedListener',
                    'queue' => 'test-queue-1',
                    'connection' => 'rabbitmq',
                    'class' => 'Data\Type',
                    'method' => 'processMessage',
                    'max_messages' => null
                ],
                'customerDeletedListener' => [
                    'name' => 'customerDeletedListener',
                    'queue' => 'test-queue-2',
                    'connection' => 'db',
                    'class' => 'Other\Type',
                    'method' => 'processMessage2',
                    'max_messages' => '98765'
                ],
                'cartCreatedListener' => [
                    'name' => 'cartCreatedListener',
                    'queue' => 'test-queue-3',
                    'connection' => 'rabbitmq',
                    'class' => 'Other\Type',
                    'method' => 'processMessage3',
                    'max_messages' => null
                ],
            ],
            'binds' => [
                ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created"],
                ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one"],
                ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one.two"],
                ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.two"],
                ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.updated"],
                ['queue' => "test-queue-1", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
                ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.created"],
                ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.deleted"],
                ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created"],
                ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created.one"],
                ['queue' => "test-queue-3", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
                ['queue' => "test-queue-4", 'exchange' => "magento", 'topic' => "customer.*"],
                ['queue' => "test-queue-5", 'exchange' => "magento", 'topic' => "customer.#"],
                ['queue' => "test-queue-6", 'exchange' => "magento", 'topic' => "customer.*.one"],
                ['queue' => "test-queue-7", 'exchange' => "magento", 'topic' => "*.created.*"],
                ['queue' => "test-queue-8", 'exchange' => "magento", 'topic' => "*.created.#"],
                ['queue' => "test-queue-9", 'exchange' => "magento", 'topic' => "#"],
            ],
            'exchange_topic_to_queues_map' => [
                'magento--customer.created' => ['test-queue-1', 'test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
                'magento--customer.created.one' => ['test-queue-1', 'test-queue-5', 'test-queue-6', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
                'magento--customer.created.one.two' => ['test-queue-1', 'test-queue-5', 'test-queue-8', 'test-queue-9'],
                'magento--customer.created.two' => ['test-queue-1', 'test-queue-5', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
                'magento--customer.updated' => ['test-queue-1', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
                'test-exchange-1--cart.created' => ['test-queue-1', 'test-queue-3'],
                'magento--customer.deleted' => ['test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
                'magento--cart.created' => ['test-queue-3', 'test-queue-9'],
                'magento--cart.created.one' => ['test-queue-3', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
            ]
        ];
    }
}

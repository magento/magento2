<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Config;

use Magento\Framework\Amqp\Config\Converter;

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
        $customizedConsumer = 'customer_deleted_listener';
        $customConnection = 'test-queue-3';
        $envConsumersConfig = [
            'consumers' => [
                $customizedConsumer => ['connection' => $customConnection],
            ]
        ];
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Converter::ENV_QUEUE)
            ->willReturn($envConsumersConfig);
        $expected = $this->getConvertedQueueConfig();
        $expected[Converter::CONSUMERS][$customizedConsumer][Converter::CONSUMER_CONNECTION] = $customConnection;
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
            ],
            'topics' => [
                'customer.created' => [
                    'name' => 'customer.created',
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
            ],
            'consumers' => [
                'customer_created_listener' => [
                    'name' => 'customer_created_listener',
                    'queue' => 'test-queue-1',
                    'connection' => 'rabbitmq',
                    'class' => 'Data\Type',
                    'method' => 'processMessage'
                ],
                'customer_deleted_listener' => [
                    'name' => 'customer_deleted_listener',
                    'queue' => 'test-queue-2',
                    'connection' => 'db',
                    'class' => 'Other\Type',
                    'method' => 'processMessage2'
                ],
            ],
            'binds' => [
                ['queue' => "test-queue-1", 'exchange' => "magento",  'topic' => "customer.created"],
                ['queue' => "test-queue-2", 'exchange' => "magento",  'topic' => "customer.created"]
            ]
        ];
    }
}

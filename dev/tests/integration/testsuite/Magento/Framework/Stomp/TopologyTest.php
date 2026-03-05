<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Stomp;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @see dev/tests/integration/_files/Magento/TestModuleMessageQueueConfiguration
 */
class TopologyTest extends TestCase
{
    /**
     * List of declared queues.
     *
     * @var array
     */
    private $declaredQueues;

    /**
     * @var Stomp
     */
    private $helper;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var DefaultValueProvider $defaultValueProvider */
        $defaultValueProvider = $this->objectManager->get(DefaultValueProvider::class);
        $this->connectionType = $defaultValueProvider->getConnection();

        if ($this->connectionType === 'stomp') {
            $this->helper = $this->objectManager->create(Stomp::class);

            if (!$this->helper->isAvailable()) {
                $this->fail('This test relies on ActiveMq JMX/Jalokia.');
            }
            $this->declaredQueues = $this->helper->getQueues();
        }
    }

    /**
     * @dataProvider queueDataProvider
     * @param array $expectedConfig
     */
    public function testTopologyInstallation(array $expectedConfig): void
    {
        if ($this->connectionType === 'amqp') {
            $this->markTestSkipped('STOMP test skipped because AMQP connection is available.
            This test is STOMP-specific.');
        }

        $name = $expectedConfig['name'];
        $this->assertArrayHasKey($name, $this->declaredQueues);

        $allowedKeys = ['name', 'durable', 'autoDelete', 'internalQueue', 'routingType'];
        // Keep only the allowed keys
        $filtered = array_intersect_key($this->declaredQueues[$name], array_flip($allowedKeys));

        $this->assertEquals(
            $expectedConfig,
            $filtered,
            'Invalid queue configuration: ' . $name
        );
    }

    /**
     * @return array
     */
    public static function queueDataProvider(): array
    {
        return [
            'stomp.consumer.config.queue' => [
                'expectedConfig' => [
                    'name' => 'stomp.consumer.config.queue',
                    'durable' => 'true',
                    'autoDelete' => 'false',
                    'internalQueue' => 'false',
                    'routingType' => 'ANYCAST'
                ]
            ]
        ];
    }
}

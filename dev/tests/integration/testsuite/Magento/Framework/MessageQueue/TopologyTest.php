<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Magento\TestFramework\Helper\Amqp;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @see dev/tests/integration/_files/Magento/TestModuleMessageQueueConfiguration
 * @see dev/tests/integration/_files/Magento/TestModuleMessageQueueConfigOverride
 */
class TopologyTest extends TestCase
{
    /**
     * List of declared exchanges.
     *
     * @var array
     */
    private $declaredExchanges;

    /**
     * @var Amqp
     */
    private $helper;

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var DefaultValueProvider $defaultValueProvider */
        $defaultValueProvider = $objectManager->get(DefaultValueProvider::class);
        $this->connectionType = $defaultValueProvider->getConnection();

        if ($this->connectionType === 'amqp') {
            $this->helper = Bootstrap::getObjectManager()->create(Amqp::class);

            if (!$this->helper->isAvailable()) {
                $this->fail('This test relies on RabbitMQ Management Plugin.');
            }

            $this->declaredExchanges = $this->helper->getExchanges();
        }
    }

    /**
     * @dataProvider exchangeDataProvider
     * @param array $expectedConfig
     * @param array $bindingConfig
     */
    public function testTopologyInstallation(array $expectedConfig, array $bindingConfig): void
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $name = $expectedConfig['name'];
        $this->assertArrayHasKey($name, $this->declaredExchanges);
        unset(
            $this->declaredExchanges[$name]['message_stats'],
            $this->declaredExchanges[$name]['user_who_performed_action'],
            $this->declaredExchanges[$name]['policy']
        );

        $this->assertEquals(
            $expectedConfig,
            $this->declaredExchanges[$name],
            'Invalid exchange configuration: ' . $name
        );

        $bindings = $this->helper->getExchangeBindings($name);
        $bindings = array_map(static function ($value) {
            unset($value['properties_key']);
            return $value;
        }, $bindings);

        $this->assertEquals(
            $bindingConfig,
            $bindings,
            'Invalid exchange bindings configuration: ' . $name
        );
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function exchangeDataProvider(): array
    {
        $virtualHost = defined('RABBITMQ_VIRTUALHOST') ? RABBITMQ_VIRTUALHOST : Amqp::DEFAULT_VIRTUALHOST;
        return [
            'magento-topic-based-exchange1' => [
                'expectedConfig' => [
                    'name' => 'magento-topic-based-exchange1',
                    'vhost' => $virtualHost,
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [
                        'alternate-exchange' => 'magento-log-exchange'
                    ],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange1',
                        'vhost' => $virtualHost,
                        'destination' => 'topic-queue1',
                        'destination_type' => 'queue',
                        'routing_key' => 'anotherTopic1',
                        'arguments' => [
                            'argument1' => 'value'
                        ],
                    ],
                ]
            ],
            'magento-topic-based-exchange2' => [
                'expectedConfig' => [
                    'name' => 'magento-topic-based-exchange2',
                    'vhost' => $virtualHost,
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [
                        'alternate-exchange' => 'magento-log-exchange',
                        'arrayValue' => ['10', '20']
                    ],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange2',
                        'vhost' => $virtualHost,
                        'destination' => 'topic-queue2',
                        'destination_type' => 'queue',
                        'routing_key' => 'anotherTopic2',
                        'arguments' => [
                            'argument1' => 'value',
                            'argument2' => true,
                            'argument3' => 150,
                        ],
                    ],
                ]
            ],
            'magento-topic-based-exchange3' => [
                'expectedConfig' => [
                    'name' => 'magento-topic-based-exchange3',
                    'vhost' => $virtualHost,
                    'type' => 'topic',
                    'durable' => false,
                    'auto_delete' => true,
                    'internal' => true,
                    'arguments' => [],
                ],
                'bindingConfig' => [],
            ],
            'magento-topic-based-exchange4' => [
                'expectedConfig' => [
                    'name' => 'magento-topic-based-exchange4',
                    'vhost' => $virtualHost,
                    'type' => 'topic',
                    'durable' => true,
                    'auto_delete' => false,
                    'internal' => false,
                    'arguments' => [],
                ],
                'bindingConfig' => [
                    [
                        'source' => 'magento-topic-based-exchange4',
                        'vhost' => $virtualHost,
                        'destination' => 'topic-queue1',
                        'destination_type' => 'queue',
                        'routing_key' => '#',
                        'arguments' => [
                            'test' => 'one'
                        ],
                    ],
                    [
                        'source' => 'magento-topic-based-exchange4',
                        'vhost' => $virtualHost,
                        'destination' => 'topic-queue2',
                        'destination_type' => 'queue',
                        'routing_key' => '*.*.*',
                        'arguments' => [],
                    ],
                ]
            ],
        ];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Config\Topology;

use Magento\Framework\MessageQueue\Config\Topology\ConfigReaderPlugin as TopologyConfigReaderPlugin;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Topology\Config\CompositeReader as TopologyConfigCompositeReader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigReaderPluginTest extends TestCase
{
    /**
     * @var TopologyConfigReaderPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var TopologyConfigCompositeReader|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(TopologyConfigCompositeReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBinds', 'getConnectionByTopic'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            TopologyConfigReaderPlugin::class,
            ['queueConfig' => $this->configMock]
        );
    }

    public function testAfterRead()
    {
        $binding = [
            [
                'queue' => 'catalog_product_removed_queue',
                'exchange' => 'magento-db',
                'topic' => 'catalog.product.removed'
            ],
            [
                'queue' => 'inventory_qty_counter_queue',
                'exchange' => 'magento',
                'topic' => 'inventory.counter.updated'
            ]
        ];
        $magento = [
            'name' => 'magento',
            'type' => 'topic',
            'connection' => 'amqp',
            'bindings' => []
        ];
        $dbDefaultBinding = [
            'id' => 'defaultBinding',
            'destinationType' => 'queue',
            'destination' => 'catalog_product_removed_queue',
            'topic' => 'catalog.product.removed',
        ];
        $amqpDefaultBinding = [
            'id' => 'defaultBinding',
            'destinationType' => 'queue',
            'destination' => 'inventory_qty_counter_queue',
            'topic' => 'inventory.counter.updated',
        ];
        $result = [
            'magento' => $magento,
            'magento-db--db' => [
                'name' => 'magento-db',
                'type' => 'topic',
                'connection' => 'db',
                'bindings' => [
                    'defaultBinding' => $dbDefaultBinding
                ]
            ],
            'magento--amqp' => [
                'name' => 'magento',
                'type' => 'topic',
                'connection' => 'amqp',
                'bindings' => [
                    'defaultBinding' => $amqpDefaultBinding
                ]
            ]
        ];
        $expectedResult = [
            'magento' => $magento,
            'magento-db--db' => [
                'name' => 'magento-db',
                'type' => 'topic',
                'connection' => 'db',
                'bindings' => [
                    'queue--catalog_product_removed_queue--catalog.product.removed' => [
                        'id' => 'queue--catalog_product_removed_queue--catalog.product.removed',
                        'destinationType' => 'queue',
                        'destination' => 'catalog_product_removed_queue',
                        'disabled' => false,
                        'topic' => 'catalog.product.removed',
                        'arguments' => []
                    ],
                    'defaultBinding' => $dbDefaultBinding
                ]
            ],
            'magento--amqp' => [
                'name' => 'magento',
                'type' => 'topic',
                'connection' => 'amqp',
                'bindings' => [
                    'queue--inventory_qty_counter_queue--inventory.counter.updated' => [
                        'id' => 'queue--inventory_qty_counter_queue--inventory.counter.updated',
                        'destinationType' => 'queue',
                        'destination' => 'inventory_qty_counter_queue',
                        'disabled' => false,
                        'topic' => 'inventory.counter.updated',
                        'arguments' => []
                    ],
                    'defaultBinding' => $amqpDefaultBinding
                ]
            ]
        ];

        $this->configMock->expects(static::atLeastOnce())
            ->method('getBinds')
            ->willReturn($binding);
        $this->configMock->expects(static::exactly(2))
            ->method('getConnectionByTopic')
            ->willReturnMap([
                ['catalog.product.removed', 'db'],
                ['inventory.counter.updated', 'amqp']
            ]);

        $this->assertEquals($expectedResult, $this->plugin->afterRead($this->subjectMock, $result));
    }
}

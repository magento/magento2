<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Consumer;

use Magento\Framework\MessageQueue\Config\Consumer\ConfigReaderPlugin as ConsumerConfigReaderPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\MessageQueue\ConfigInterface;
use Magento\Framework\MessageQueue\Consumer\Config\CompositeReader as ConsumerConfigCompositeReader;

class ConfigReaderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConsumerConfigReaderPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var ConsumerConfigCompositeReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(ConsumerConfigCompositeReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            ConsumerConfigReaderPlugin::class,
            ['config' => $this->configMock]
        );
    }

    public function testAfterRead()
    {
        $result = ['consumer0' => []];
        $consumers = [
            [
                'name' => 'consumer1',
                'handlers' => [
                    ['handlerConfig1_1_1', 'handlerConfig1_1_2'],
                    ['handlerConfig1_2_1']
                ],
                'queue' => ['item1_1', 'item1_2'],
                'instance_type' => 'type1',
                'connection' => 'connection1',
                'max_messages' => 100
            ],
            [
                'name' => 'consumer2',
                'handlers' => [],
                'queue' => ['item2_1'],
                'instance_type' => 'type2',
                'connection' => 'connection2',
                'max_messages' => 2
            ]
        ];
        $finalResult = [
            'consumer1' => [
                'name' => 'consumer1',
                'queue' => ['item1_1', 'item1_2'],
                'consumerInstance' => 'type1',
                'handlers' => ['handlerConfig1_1_1', 'handlerConfig1_1_2', 'handlerConfig1_2_1'],
                'connection' => 'connection1',
                'maxMessages' => 100
            ],
            'consumer2' => [
                'name' => 'consumer2',
                'queue' => ['item2_1'],
                'consumerInstance' => 'type2',
                'handlers' => [],
                'connection' => 'connection2',
                'maxMessages' => 2
            ],
            'consumer0' => []
        ];

        $this->configMock->expects(static::atLeastOnce())
            ->method('getConsumers')
            ->willReturn($consumers);

        $this->assertEquals($finalResult, $this->plugin->afterRead($this->subjectMock, $result));
    }
}

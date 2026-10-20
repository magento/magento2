<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Xml;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Topology\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var MockObject
     */
    private $interpreter;

    /**
     * @var MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->defaultConfigProviderMock =
            $this->createMock(DefaultValueProvider::class);
        $this->interpreter = $this->createMock(InterpreterInterface::class);
        $this->converter = new Converter(new BooleanUtils(), $this->interpreter, $this->defaultConfigProviderMock);
        $this->defaultConfigProviderMock->expects($this->any())->method('getConnection')->willReturn('amqp');
    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_topology';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);

        $this->interpreter->expects($this->any())->method('evaluate')->willReturn(10);
        $result = $this->converter->convert($dom);

        $expectedData = include $fixtureDir . '/valid.php';
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }

    public function testConvertQueues(): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML(
            '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <exchange name="shared.name" type="topic" connection="amqp">
                    <binding id="bind01" topic="topic01" destinationType="queue" destination="shared.name"/>
                </exchange>
                <queue name="shared.name" connection="amqp" durable="false" autoDelete="true">
                    <arguments>
                        <argument name="x-message-ttl" xsi:type="number">1000</argument>
                    </arguments>
                </queue>
                <queue name="merged.queue">
                    <arguments>
                        <argument name="x-dead-letter-exchange" xsi:type="string">magento</argument>
                    </arguments>
                </queue>
                <queue name="merged.queue" connection="amqp" durable="false">
                    <arguments>
                        <argument name="x-max-priority" xsi:type="number">10</argument>
                    </arguments>
                </queue>
            </config>'
        );
        $this->interpreter->method('evaluate')->willReturnCallback(
            static fn (array $data) => $data['value']
        );

        $result = $this->converter->convert($dom);

        $this->assertSame(
            ['shared.name--amqp', 'queue--shared.name--amqp', 'queue--merged.queue--amqp'],
            array_keys($result)
        );
        $this->assertSame('topic', $result['shared.name--amqp']['type']);
        $this->assertSame(
            [
                'name' => 'shared.name',
                'type' => 'queue',
                'connection' => 'amqp',
                'durable' => false,
                'autoDelete' => true,
                'arguments' => ['x-message-ttl' => '1000'],
            ],
            $result['queue--shared.name--amqp']
        );
        $this->assertSame(
            [
                'name' => 'merged.queue',
                'type' => 'queue',
                'connection' => 'amqp',
                'durable' => false,
                'autoDelete' => false,
                'arguments' => ['x-dead-letter-exchange' => 'magento', 'x-max-priority' => '10'],
            ],
            $result['queue--merged.queue--amqp']
        );
    }
}

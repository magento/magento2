<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\TestFramework\Helper\Bootstrap;

class RpcCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * @var string[]
     */
    protected $consumers = ['synchronousRpcTestConsumer'];

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var DefaultValueProvider $defaultValueProvider */
        $defaultValueProvider = $this->objectManager->get(DefaultValueProvider::class);
        $this->connectionType = $defaultValueProvider->getConnection();

        if ($this->connectionType === 'amqp') {
            parent::setUp();
        }
    }

    /**
     * Verify that RPC call based on Rabbit MQ is processed correctly.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test RPC.
     */
    public function testSynchronousRpcCommunication()
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $input = 'Input value';
        $response = $this->publisher->publish('synchronous.rpc.test', $input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

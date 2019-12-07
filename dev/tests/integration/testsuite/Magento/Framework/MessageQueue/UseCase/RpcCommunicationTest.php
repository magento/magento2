<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

class RpcCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $consumers = ['synchronousRpcTestConsumer'];

    /**
     * Verify that RPC call based on Rabbit MQ is processed correctly.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test RPC.
     */
    public function testSynchronousRpcCommunication()
    {
        $input = 'Input value';
        $response = $this->publisher->publish('synchronous.rpc.test', $input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

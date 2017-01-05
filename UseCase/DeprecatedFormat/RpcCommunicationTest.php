<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase\DeprecatedFormat;

use Magento\Framework\MessageQueue\UseCase\QueueTestCaseAbstract;

class RpcCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $consumers = ['synchronousRpcTestConsumer.deprecated'];

    /**
     * Verify that RPC call based on Rabbit MQ is processed correctly.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test RPC.
     */
    public function testSynchronousRpcCommunication()
    {
        $input = 'Input value';
        $response = $this->publisher->publish('synchronous.rpc.test.deprecated', $input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

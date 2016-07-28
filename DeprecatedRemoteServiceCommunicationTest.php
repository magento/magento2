<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use \Magento\Framework\MessageQueue\QueueTestCaseAbstract;
use \Magento\TestModuleSynchronousAmqp\Api\ServiceInterface;

class DeprecatedRemoteServiceCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $consumers = ['RemoteServiceTestConsumer.deprecated'];

    public function testRemoteServiceCommunication()
    {
        $input = 'Input value';
        /** @var ServiceInterface $generatedRemoteService */
        $generatedRemoteService = $this->objectManager->get(ServiceInterface::class);
        $response = $generatedRemoteService->execute($input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

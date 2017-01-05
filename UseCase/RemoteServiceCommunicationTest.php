<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\TestModuleSynchronousAmqp\Api\ServiceInterface;

class RemoteServiceCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $consumers = ['RemoteServiceTestConsumer'];

    public function testRemoteServiceCommunication()
    {
        $input = 'Input value';
        /** @var ServiceInterface $generatedRemoteService */
        $generatedRemoteService = $this->objectManager->get(ServiceInterface::class);
        $response = $generatedRemoteService->execute($input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

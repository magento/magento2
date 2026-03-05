<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleSynchronousAmqp\Api\ServiceInterface;

class RemoteServiceCommunicationTest extends QueueTestCaseAbstract
{
    /**
     * @var string[]
     */
    protected $consumers = ['RemoteServiceTestConsumer'];

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $this->objectManager = Bootstrap::getObjectManager();
        /** @var DefaultValueProvider $defaultValueProvider */
        $defaultValueProvider = $this->objectManager->get(DefaultValueProvider::class);
        $this->connectionType = $defaultValueProvider->getConnection();

        if ($this->connectionType === 'amqp') {
            parent::setUp();
        }
    }

    public function testRemoteServiceCommunication()
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $input = 'Input value';
        /** @var ServiceInterface $generatedRemoteService */
        /** @phpstan-ignore-next-line */
        $generatedRemoteService = $this->objectManager->get(ServiceInterface::class);
        $response = $generatedRemoteService->execute($input);
        $this->assertEquals($input . ' processed by RPC handler', $response);
    }
}

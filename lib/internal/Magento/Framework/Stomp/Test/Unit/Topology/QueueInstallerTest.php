<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit\Topology;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\Stomp\StompClient;
use Magento\Framework\Stomp\StompClientFactory;
use Magento\Framework\Stomp\Topology\QueueInstaller;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stomp\Transport\Message;

class QueueInstallerTest extends TestCase
{
    /** @var MockObject| StompClientFactory */
    private StompClientFactory|MockObject $stompClientFactory;

    /** @var MockObject| LoggerInterface */
    private LoggerInterface|MockObject $logger;

    /** @var MockObject| StompClient */
    private StompClient|MockObject $stompClient;

    /** @var MockObject| QueueConfigItemInterface */
    private QueueConfigItemInterface|MockObject $queueConfig;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->stompClientFactory = $this->createMock(StompClientFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->stompClient = $this->createMock(StompClient::class);
        $this->queueConfig = $this->createMock(QueueConfigItemInterface::class);

        $this->stompClientFactory
            ->method('create')
            ->willReturn($this->stompClient);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testInstallSuccess(): void
    {
        $queueName = 'queue.test';

        $this->queueConfig
            ->method('getName')
            ->willReturn($queueName);

        $this->stompClient
            ->expects($this->once())
            ->method('send')
            ->with(
                $queueName,
                $this->callback(function (Message $message) {
                    return $message->getBody() === 'queue-created' &&
                        $message->getHeaders()['destination-type'] === QueueInstaller::DESTINATION_TYPE &&
                        isset($message->getHeaders()['expires']);
                })
            );

        $this->stompClient
            ->expects($this->once())
            ->method('subscribeQueue')
            ->with($queueName);

        $this->stompClient
            ->expects($this->once())
            ->method('readMessage');

        $installer = new QueueInstaller($this->stompClientFactory, $this->logger);
        $installer->install($this->queueConfig);
    }

    /**
     * @return void
     */
    public function testInstallFailureLogsException(): void
    {
        $queueName = 'queue.failure';
        $exception = new \RuntimeException('Simulated failure');

        $this->queueConfig
            ->method('getName')
            ->willReturn($queueName);

        $this->stompClientFactory
            ->method('create')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                sprintf('Queue installation failed for "%s": %s', $queueName, $exception->getMessage()),
                ['exception' => $exception]
            );

        $installer = new QueueInstaller($this->stompClientFactory, $this->logger);
        $installer->install($this->queueConfig);
    }
}

<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit\Topology;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\Stomp\StompClientInterface;
use Magento\Framework\Stomp\StompClientProvider;
use Magento\Framework\Stomp\Topology\QueueInstaller;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class QueueInstallerTest extends TestCase
{
    /** @var MockObject| StompClientProvider */
    private StompClientProvider|MockObject $stompClientProvider;

    /** @var MockObject| LoggerInterface */
    private LoggerInterface|MockObject $logger;

    /** @var MockObject| StompClientInterface */
    private StompClientInterface|MockObject $stompClient;

    /** @var MockObject| QueueConfigItemInterface */
    private QueueConfigItemInterface|MockObject $queueConfig;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->stompClientProvider = $this->createMock(StompClientProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->stompClient = $this->createMock(StompClientInterface::class);
        $this->queueConfig = $this->createMock(QueueConfigItemInterface::class);

        $this->stompClientProvider
            ->method('get')
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

        $this->queueConfig->method('getConnection')->willReturn('custom-stomp');
        $this->stompClientProvider->expects($this->once())->method('get')->with('custom-stomp');
        $this->logger->expects($this->never())->method('error');
        $this->stompClient
            ->expects($this->once())
            ->method('createQueue')
            ->with($queueName);

        $this->stompClient->expects($this->never())->method('send');
        $this->stompClient
            ->expects($this->never())
            ->method('subscribeQueue');

        $this->stompClient
            ->expects($this->never())
            ->method('readMessage');

        $installer = new QueueInstaller($this->stompClientProvider, $this->logger);
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

        $this->queueConfig->method('getConnection')->willReturn('stomp');
        $this->stompClientProvider
            ->method('get')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                sprintf('Queue installation failed for "%s": %s', $queueName, $exception->getMessage()),
                ['exception' => $exception]
            );

        $installer = new QueueInstaller($this->stompClientProvider, $this->logger);
        $installer->install($this->queueConfig);
    }

    public function testQueueDeclarationFailureLogsException(): void
    {
        $exception = new \Magento\Framework\Stomp\Exception\ClientException('Declaration failed');
        $this->queueConfig->method('getName')->willReturn('queue.failure');
        $this->queueConfig->method('getConnection')->willReturn('stomp');
        $this->stompClient->method('createQueue')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error')->with(
            'Queue installation failed for "queue.failure": Declaration failed',
            ['exception' => $exception]
        );

        $installer = new QueueInstaller($this->stompClientProvider, $this->logger);
        $installer->install($this->queueConfig);
    }
}

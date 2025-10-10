<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit\Bulk;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfigInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface as BaseQueueInterface;
use Magento\Framework\Stomp\Bulk\Queue;
use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\StompClient;
use Magento\Framework\Stomp\StompClientFactory;
use PHPUnit\Framework\TestCase;
use Stomp\Transport\Message;

class QueueTest extends TestCase
{
    /** @var Config|\PHPUnit\Framework\MockObject\MockObject */
    private Config $stompConfig;

    /** @var StompClientFactory|\PHPUnit\Framework\MockObject\MockObject */
    private StompClientFactory $stompClientFactory;

    /** @var StompClient|\PHPUnit\Framework\MockObject\MockObject */
    private StompClient $stompClient;

    /** @var CommunicationConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private CommunicationConfigInterface $communicationConfig;

    /** @var BaseQueueInterface|\PHPUnit\Framework\MockObject\MockObject */
    private BaseQueueInterface $baseQueue;

    /** @var string */
    private string $queueName = 'bulk.queue';

    protected function setUp(): void
    {
        $this->stompConfig = $this->createMock(Config::class);
        $this->stompClient = $this->createMock(StompClient::class);
        $this->stompClientFactory = $this->createMock(StompClientFactory::class);
        $this->communicationConfig = $this->createMock(CommunicationConfigInterface::class);
        $this->baseQueue = $this->createMock(BaseQueueInterface::class);

        $this->stompClientFactory
            ->method('create')
            ->willReturn($this->stompClient);
            
        $this->communicationConfig
            ->method('getTopic')
            ->with('some.topic')
            ->willReturn([
                CommunicationConfigInterface::TOPIC_IS_SYNCHRONOUS => false
            ]);
    }

    public function testPushSendsAllEnvelopes(): void
    {
        $envelopes = [
            $this->createEnvelopeMock('{"bulk": 1}', ['persistent' => true]),
            $this->createEnvelopeMock('{"bulk": 2}', ['persistent' => true])
        ];

        $callHistory = [];

        $this->stompClient
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturnCallback(function (string $queueName, Message $message) use (&$callHistory) {
                $callHistory[] = [
                    'queue' => $queueName,
                    'body' => $message->getBody(),
                    'headers' => $message->getHeaders()
                ];
            });

        $queue = new Queue(
            $this->stompConfig,
            $this->queueName,
            $this->stompClientFactory,
            $this->communicationConfig
        );

        $queue->push($this->baseQueue, 'some.topic', $envelopes);

        $this->assertCount(2, $callHistory);

        $this->assertSame($this->queueName, $callHistory[0]['queue']);
        $this->assertSame('{"bulk": 1}', $callHistory[0]['body']);
        $this->assertSame(['persistent' => true], $callHistory[0]['headers']);

        $this->assertSame($this->queueName, $callHistory[1]['queue']);
        $this->assertSame('{"bulk": 2}', $callHistory[1]['body']);
        $this->assertSame(['persistent' => true], $callHistory[1]['headers']);
    }

    private function createEnvelopeMock(string $body, array $properties): EnvelopeInterface
    {
        $envelope = $this->createMock(EnvelopeInterface::class);
        $envelope->method('getBody')->willReturn($body);
        $envelope->method('getProperties')->willReturn($properties);
        return $envelope;
    }
}

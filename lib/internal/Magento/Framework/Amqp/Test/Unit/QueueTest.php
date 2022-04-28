<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config;
use Magento\Framework\Amqp\Queue;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class QueueTest extends TestCase
{
    private const PREFETCH_COUNT = 100;
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var EnvelopeFactory|MockObject
     */
    private $envelopeFactory;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Queue
     */
    private $model;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->envelopeFactory = $this->createMock(EnvelopeFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->model = new Queue(
            $this->config,
            $this->envelopeFactory,
            'testQueue',
            $this->logger,
            self::PREFETCH_COUNT
        );
    }

    /**
     * Test verifies that prefetch value is used to specify how many messages
     * are being sent to the consumer at the same time.
     */
    public function testSubscribe()
    {
        $callback = function () {
        };
        $amqpChannel = $this->createMock(AMQPChannel::class);
        $amqpChannel->expects($this->once())
            ->method('basic_qos')
            ->with(0, self::PREFETCH_COUNT, false);
        $this->config->expects($this->once())
            ->method('getChannel')
            ->willReturn($amqpChannel);

        $this->model->subscribe($callback);
    }
}

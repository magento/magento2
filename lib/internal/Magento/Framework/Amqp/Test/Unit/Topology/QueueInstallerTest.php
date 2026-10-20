<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit\Topology;

use Magento\Framework\Amqp\Topology\QueueInstaller;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;

class QueueInstallerTest extends TestCase
{
    public function testInstall()
    {
        $model = new QueueInstaller();
        $channel = $this->createMock(AMQPChannel::class);

        $queue = $this->createMock(QueueConfigItemInterface::class);
        $queue->expects($this->once())->method('getName')->willReturn('queue01');
        $queue->expects($this->once())->method('isDurable')->willReturn(true);
        $queue->expects($this->once())->method('isAutoDelete')->willReturn(false);
        $queue->expects($this->once())->method('getArguments')->willReturn(['some' => 'value']);

        $channel->expects($this->once())
            ->method('queue_declare')
            ->with('queue01', false, true, false, false, false, ['some' => ['S', 'value']], null);
        $model->install($channel, $queue);
    }

    public function testInstallDeclaredQueue(): void
    {
        $queue = new QueueConfigItem();
        $queue->setData(
            [
                'name' => 'retry.queue',
                'connection' => 'amqp',
                'durable' => false,
                'autoDelete' => true,
                'arguments' => ['x-message-ttl' => '1000', 'x-dead-letter-exchange' => 'magento'],
            ]
        );
        $channel = $this->createMock(AMQPChannel::class);
        $channel->expects($this->once())
            ->method('queue_declare')
            ->with(
                'retry.queue',
                false,
                false,
                false,
                true,
                false,
                ['x-message-ttl' => ['I', 1000], 'x-dead-letter-exchange' => ['S', 'magento']]
            );

        (new QueueInstaller())->install($channel, $queue);
    }
}

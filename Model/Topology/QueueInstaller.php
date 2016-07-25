<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model\Topology;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;

/**
 * Queue installer.
 */
class QueueInstaller
{
    use ArgumentProcessor;

    /**
     * Install queue.
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param QueueConfigItemInterface $queue
     * @return void
     */
    public function install(\PhpAmqpLib\Channel\AMQPChannel $channel, QueueConfigItemInterface $queue)
    {
        $channel->queue_declare(
            $queue->getName(),
            false,
            $queue->isDurable(),
            false,
            $queue->isAutoDelete(),
            false,
            $this->processArguments($queue->getArguments())
        );
    }
}

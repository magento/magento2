<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology\BindingInstallerType;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\Amqp\Topology\ArgumentProcessor;

/**
 * {@inheritdoc}
 */
class Queue implements BindingInstallerInterface
{
    use ArgumentProcessor;

    /**
     * {@inheritdoc}
     */
    public function install(AMQPChannel $channel, BindingInterface $binding, $exchangeName)
    {
        $channel->queue_bind(
            $binding->getDestination(),
            $exchangeName,
            $binding->getTopic(),
            false,
            $this->processArguments($binding->getArguments())
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology\BindingInstallerType;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use Magento\Framework\Amqp\Topology\BindingInstallerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use Magento\Framework\Amqp\Topology\ArgumentProcessor;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class Queue implements BindingInstallerInterface
{
    use ArgumentProcessor;

    /**
     * {@inheritdoc}
     * @since 2.2.0
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

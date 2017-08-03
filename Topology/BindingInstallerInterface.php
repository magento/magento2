<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Exchange binding installer.
 * @since 2.2.0
 */
interface BindingInstallerInterface
{
    /**
     * Install exchange bindings.
     *
     * @param AMQPChannel $channel
     * @param BindingInterface $binding
     * @param string $exchangeName
     * @return void
     * @since 2.2.0
     */
    public function install(AMQPChannel $channel, BindingInterface $binding, $exchangeName);
}

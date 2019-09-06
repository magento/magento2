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
     */
    public function install(AMQPChannel $channel, BindingInterface $binding, $exchangeName);
}

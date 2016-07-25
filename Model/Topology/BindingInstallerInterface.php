<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Model\Topology;

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
     * @return void
     */
    public function install(AMQPChannel $channel, BindingInterface $binding, $exchangeName);
}

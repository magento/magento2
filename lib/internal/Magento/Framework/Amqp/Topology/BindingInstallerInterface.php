<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Exchange binding installer.
 *
 * @api
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

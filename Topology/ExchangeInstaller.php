<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;

/**
 * Exchange installer.
 */
class ExchangeInstaller
{
    use ArgumentProcessor;

    /**
     * @var BindingInstallerInterface
     */
    private $bindingInstaller;

    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface $bindingInstaller
     */
    public function __construct(BindingInstallerInterface $bindingInstaller)
    {
        $this->bindingInstaller = $bindingInstaller;
    }

    /**
     * Install exchange.
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param ExchangeConfigItemInterface $exchange
     * @return void
     */
    public function install(\PhpAmqpLib\Channel\AMQPChannel $channel, ExchangeConfigItemInterface $exchange)
    {
        $channel->exchange_declare(
            $exchange->getName(),
            $exchange->getType(),
            false,
            $exchange->isDurable(),
            $exchange->isAutoDelete(),
            $exchange->isInternal(),
            false,
            $this->processArguments($exchange->getArguments())
        );

        foreach ($exchange->getBindings() as $binding) {
            $this->bindingInstaller->install($channel, $binding, $exchange->getName());
        }
    }
}

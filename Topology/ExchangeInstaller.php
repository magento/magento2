<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;

/**
 * Exchange installer.
 * @since 2.2.0
 */
class ExchangeInstaller
{
    use ArgumentProcessor;

    /**
     * @var BindingInstallerInterface
     * @since 2.2.0
     */
    private $bindingInstaller;

    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface $bindingInstaller
     * @since 2.2.0
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
     * @since 2.2.0
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

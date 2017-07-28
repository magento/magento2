<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class BindingInstaller implements BindingInstallerInterface
{
    /**
     * @var BindingInstallerInterface[]
     * @since 2.2.0
     */
    private $installers;

    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface[] $installers
     * @since 2.2.0
     */
    public function __construct(array $installers)
    {
        $this->installers = $installers;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function install(AMQPChannel $channel, BindingInterface $binding, $exchangeName)
    {
        $this->getInstaller($binding->getDestinationType())->install($channel, $binding, $exchangeName);
    }

    /**
     * Get binding installer by type.
     *
     * @param string $type
     * @return BindingInstallerInterface
     * @since 2.2.0
     */
    private function getInstaller($type)
    {
        if (!isset($this->installers[$type])) {
            throw new \InvalidArgumentException(sprintf('Installer type [%s] is not configured', $type));
        }
        return $this->installers[$type];
    }
}

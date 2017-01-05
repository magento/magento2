<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingInterface;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * {@inheritdoc}
 */
class BindingInstaller implements BindingInstallerInterface
{
    /**
     * @var BindingInstallerInterface[]
     */
    private $installers;

    /**
     * Initialize dependencies.
     *
     * @param BindingInstallerInterface[] $installers
     */
    public function __construct(array $installers)
    {
        $this->installers = $installers;
    }

    /**
     * {@inheritdoc}
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
     */
    private function getInstaller($type)
    {
        if (!isset($this->installers[$type])) {
            throw new \InvalidArgumentException(sprintf('Installer type [%s] is not configured', $type));
        }
        return $this->installers[$type];
    }
}

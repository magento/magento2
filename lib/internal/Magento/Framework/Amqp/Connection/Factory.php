<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Connection;

use Magento\Framework\App\ObjectManager;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Create connection based on options.
 */
class Factory
{
    /**
     * Create connection according to given options.
     *
     * @param FactoryOptions $options
     * @return AbstractConnection
     */
    public function create(FactoryOptions $options): AbstractConnection
    {
        $connectionType = $options->isSslEnabled() ? AMQPSSLConnection::class : AMQPStreamConnection::class;
        $parameters = [
            'host' => $options->getHost(),
            'port' => $options->getPort(),
            'user' => $options->getUsername(),
            'password' => $options->getPassword(),
            'vhost' => $options->getVirtualHost() !== null ? $options->getVirtualHost() : '/',
        ];

        if ($options->isSslEnabled()) {
            $parameters['ssl_options'] = $options->getSslOptions() !== null
                ? $options->getSslOptions()
                : ['verify_peer' => true];
        }

        return ObjectManager::getInstance()->create($connectionType, $parameters);
    }
}

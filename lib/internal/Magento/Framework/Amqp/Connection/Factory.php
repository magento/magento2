<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Connection;

use Magento\Framework\App\ObjectManager;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AbstractConnection;

/**
 * Create connection based on options.
 */
class Factory
{
    /**
     * Create connection according to given options.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param FactoryOptions $options
     * @return AbstractConnection
     */
    public function create(FactoryOptions $options): AbstractConnection
    {
        $config = ObjectManager::getInstance()->create(AMQPConnectionConfig::class);

        // Set host, port, user, password, and vhost from options
        $config->setHost($options->getHost());
        $config->setPort((int)$options->getPort());
        $config->setUser($options->getUsername());
        $config->setPassword($options->getPassword());
        $config->setVhost($options->getVirtualHost() !== null ? $options->getVirtualHost() : '/');

        // Set SSL options if SSL is enabled
        if ($options->isSslEnabled()) {
            $config->setIsSecure(true);
            $sslOptions = $options->getSslOptions();
            if ($sslOptions) {
                if (isset($sslOptions['cafile'])) {
                    $config->setSslCaCert($sslOptions['cafile']);
                }
                if (isset($sslOptions['local_cert'])) {
                    $config->setSslCert($sslOptions['local_cert']);
                }
                if (isset($sslOptions['local_pk'])) {
                    $config->setSslKey($sslOptions['local_pk']);
                }
                if (isset($sslOptions['verify_peer'])) {
                    $config->setSslVerify($sslOptions['verify_peer']);
                }
                if (isset($sslOptions['verify_peer_name'])) {
                    $config->setSslVerifyName($sslOptions['verify_peer_name']);
                }
                if (isset($sslOptions['passphrase'])) {
                    $config->setSslPassPhrase($sslOptions['passphrase']);
                }
                if (isset($sslOptions['ciphers'])) {
                    $config->setSslCiphers($sslOptions['ciphers']);
                }
            } else {
                // Default SSL verification option
                $config->setSslVerify(true);
            }
        } else {
            $config->setIsSecure(false);
        }

        // Use the connection factory to create the connection
        return AMQPConnectionFactory::create($config);
    }
}

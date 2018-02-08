<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Validator;

/**
 * Connection validator for Redis configurations
 */
class RedisConnectionValidator
{
    /**
     * Validate redis connection
     *
     * @param array $redisOptions
     *
     * @return bool
     */
    public function isValidConnection(array $redisOptions)
    {
        $default = [
            'host'                   => '',
            'port'                   => '',
            'db'                     => '',
            'password'               => null,
            'timeout'                => null,
            'persistent'             => '',
            'sentinel_master'        => null,
            'sentinel_master_verify' => null,
        ];

        $config = array_merge($default, $redisOptions);

        try {
            // If Redis is set to use sentinel, try to retrieve master from Sentinel servers, then try connecting to it.
            if (isset($config['sentinel_master'])) {
                $sentinelClient = new \Credis_Client(
                    $config['host'],
                    $config['port'],
                    $config['timeout'],
                    $config['persistent']
                );
                $sentinelClient->forceStandalone();
                $sentinelClient->setMaxConnectRetries(0);

                $sentinel = new \Credis_Sentinel($sentinelClient);
                $sentinel
                    ->setClientTimeout($config['timeout'])
                    ->setClientPersistent($config['persistent']);

                $redisClient = $sentinel->getMasterClient($config['sentinel_master']);
                $redisClient->setMaxConnectRetries(1);
                $redisClient->connect();

                return true;
            }

            // When not using sentinel mode, just process standard check.
            $redisClient = new \Credis_Client(
                $config['host'],
                $config['port'],
                $config['timeout'],
                $config['persistent'],
                $config['db'],
                $config['password']
            );

            $redisClient->setMaxConnectRetries(1);
            $redisClient->connect();
        } catch (\CredisException $e) {
            return false;
        }

        return true;
    }
}

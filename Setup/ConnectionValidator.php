<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Setup;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class ConnectionValidator - validates Amqp related settings
 */
class ConnectionValidator
{
    /**
     * Checks Amqp Connection
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $virtualHost
     * @return bool true if the connection succeeded, false otherwise
     */
    public function isConnectionValid($host, $port, $user, $password = '', $virtualHost = '')
    {
        try {
            $connection = new AMQPStreamConnection(
                $host,
                $port,
                $user,
                $password,
                $virtualHost
            );

            $connection->close();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}

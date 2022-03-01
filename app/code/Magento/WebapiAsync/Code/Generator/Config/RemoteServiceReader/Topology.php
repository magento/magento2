<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Remote service reader with auto generated configuration for queue_topology.xml
 */
class Topology implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * @param DefaultValueProvider $defaultValueProvider
     */
    public function __construct(
        DefaultValueProvider $defaultValueProvider
    ) {
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * Generate topology configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $bindings = [];
        $destinationType = 'queue';
        $topic = 'async.#';
        $destination = 'async.operations.all';
        $bindingId = $destinationType . '--' . $destination . '--' . $topic;
        $bindings[$bindingId] = [
            'id'              => $bindingId,
            'topic'           => $topic,
            'destinationType' => $destinationType,
            'destination'     => $destination,
            'disabled'        => false,
            'arguments'       => [],
        ];

        $name = 'magento';
        $connection = $this->getConnection();
        $result[$name . '--' . $connection] = [
            'name'       => $name,
            'type'       => 'topic',
            'connection' => $connection,
            'durable'    => true,
            'autoDelete' => false,
            'arguments'  => [],
            'internal'   => false,
            'bindings'   => $bindings,
        ];

        return $result;
    }

    /**
     * Get connection
     *
     * @return string
     */
    private function getConnection()
    {
        $connection = $this->defaultValueProvider->getConnection();
        // if db connection, return amqp instead.
        return $connection === 'db' ? WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION : $connection;
    }
}

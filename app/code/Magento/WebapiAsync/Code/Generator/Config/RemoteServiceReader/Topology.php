<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Communication\Config\ReflectionGenerator;

/**
 * Remote service reader with auto generated configuration for queue_topology.xml
 */
class Topology implements \Magento\Framework\Config\ReaderInterface
{

    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;

    /**
     * @var \Magento\Framework\Communication\Config\ReflectionGenerator
     */
    private $reflectionGenerator;

    /**
     * Topology constructor.
     *
     * @param WebApiAsyncConfig $webapiAsyncConfig
     * @param ReflectionGenerator $reflectionGenerator
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig,
        ReflectionGenerator $reflectionGenerator
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->reflectionGenerator = $reflectionGenerator;
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
        $asyncServicesData = $this->webapiAsyncConfig->getServices();
        $bindings = [];
        foreach ($asyncServicesData as $serviceData) {
            $topicName = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_TOPIC];
            $bindings[$topicName] = [
                'id'              => $topicName,
                'topic'           => $topicName,
                'destinationType' => 'queue',
                'destination'     => $topicName,
                'disabled'        => false,
                'arguments'       => [],
            ];
        }

        $result = [
            'magento-async-amqp' =>
                [
                    'name'       => 'magento',
                    'type'       => 'topic',
                    'connection' => 'amqp',
                    'durable'    => true,
                    'autoDelete' => false,
                    'arguments'  => [],
                    'internal'   => false,
                    'bindings'   => $bindings,
                ],
        ];

        return $result;
    }
}

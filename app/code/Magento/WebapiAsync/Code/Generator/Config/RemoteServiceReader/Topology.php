<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\WebapiAsync\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Communication\Config\ReflectionGenerator;

/**
 * Remote service reader with auto generated configuration for queue_topology.xml
 */
class Topology implements \Magento\Framework\Config\ReaderInterface
{

    /**
     * @var \Magento\WebapiAsync\Model\ConfigInterface
     */
    private $webapiAsyncConfig;

    /**
     * @var \Magento\Framework\Communication\Config\ReflectionGenerator
     */
    private $reflectionGenerator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\WebapiAsync\Model\ConfigInterface $webapiAsyncConfig
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

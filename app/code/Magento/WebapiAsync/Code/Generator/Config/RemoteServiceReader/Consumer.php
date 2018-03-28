<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;

/**
 * Remote service reader with auto generated configuration for queue_consumer.xml
 */
class Consumer implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;

    /**
     * Initialize dependencies.
     *
     * @param WebApiAsyncConfig $webapiAsyncConfig
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
    }

    /**
     * Generate consumer configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $asyncServicesData = $this->webapiAsyncConfig->getServices();
        $result = [];
        foreach ($asyncServicesData as $serviceData) {
            $topicName = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_TOPIC];
            $serviceClass = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_INTERFACE];
            $serviceMethod = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_METHOD];

            $result[$topicName] =
                [
                    'name'             => $topicName,
                    'queue'            => $topicName,
                    'consumerInstance' => WebApiAsyncConfig::DEFAULT_CONSUMER_INSTANCE,
                    'connection'       => WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION,
                    'maxMessages'      => WebApiAsyncConfig::DEFAULT_CONSUMER_MAX_MESSAGE,
                    'handlers'         => [
                        WebApiAsyncConfig::DEFAULT_HANDLER_NAME => [
                            'type'   => $serviceClass,
                            'method' => $serviceMethod,
                        ],
                    ],
                ];
        }

        return $result;
    }
}

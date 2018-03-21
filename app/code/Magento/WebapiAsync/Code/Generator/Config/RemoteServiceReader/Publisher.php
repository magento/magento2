<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\WebapiAsync\Model\ConfigInterface as WebApiAsyncConfig;

/**
 * Remote service reader with auto generated configuration for queue_publisher.xml
 */
class Publisher implements \Magento\Framework\Config\ReaderInterface
{

    /**
     * @var \Magento\WebapiAsync\Model\ConfigInterface
     */
    private $webapiAsyncConfig;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\WebapiAsync\Model\ConfigInterface $webapiAsyncConfig
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
    }

    /**
     * Generate publisher configuration based on remote services declarations
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
            $result[$topicName] =
                [
                    'topic'       => $topicName,
                    'disabled'    => false,
                    'connections' => [
                        'amqp' => [
                            'name'     => 'amqp',
                            'exchange' => 'magento',
                            'disabled' => false,
                        ],
                    ],
                ];
        }

        return $result;
    }
}

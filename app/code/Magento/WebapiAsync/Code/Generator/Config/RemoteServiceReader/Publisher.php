<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Remote service reader with auto generated configuration for queue_publisher.xml
 */
class Publisher implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var WebApiAsyncConfig
     */
    private $webapiAsyncConfig;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * Initialize dependencies.
     *
     * @param WebApiAsyncConfig $webapiAsyncConfig
     * @param DefaultValueProvider|null $defaultValueProvider
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig,
        DefaultValueProvider $defaultValueProvider = null
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->defaultValueProvider = $defaultValueProvider
            ?? ObjectManager::getInstance()->get(DefaultValueProvider::class);
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
                        $this->defaultValueProvider->getConnection() => [
                            'name'     => $this->defaultValueProvider->getConnection(),
                            'exchange' => 'magento',
                            'disabled' => false,
                        ],
                    ],
                ];
        }

        return $result;
    }
}

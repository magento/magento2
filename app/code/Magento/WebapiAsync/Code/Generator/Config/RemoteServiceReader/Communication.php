<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\WebapiAsync\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\Communication\Config\ReflectionGenerator;

/**
 * Remote service reader with auto generated configuration for communication.xml
 */
class Communication implements \Magento\Framework\Config\ReaderInterface
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
     * @param \Magento\Framework\Communication\Config\ReflectionGenerator $reflectionGenerator
     */
    public function __construct(
        WebApiAsyncConfig $webapiAsyncConfig,
        ReflectionGenerator $reflectionGenerator
    ) {
        $this->webapiAsyncConfig = $webapiAsyncConfig;
        $this->reflectionGenerator = $reflectionGenerator;
    }

    /**
     * Generate communication configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        try {
            $asyncServicesData = $this->webapiAsyncConfig->getServices();
        } catch (\Exception $e) {
            return [];
        }
        $result = [];
        foreach ($asyncServicesData as $serviceData) {
            $topicName = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_TOPIC];
            $serviceClass = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_INTERFACE];
            $serviceMethod = $serviceData[WebApiAsyncConfig::SERVICE_PARAM_KEY_METHOD];

            $topicConfig = $this->reflectionGenerator->generateTopicConfigForServiceMethod(
                $topicName,
                $serviceClass,
                $serviceMethod,
                [
                    WebApiAsyncConfig::DEFAULT_HANDLER_NAME => [
                        CommunicationConfig::HANDLER_TYPE   => $serviceClass,
                        CommunicationConfig::HANDLER_METHOD => $serviceMethod,
                    ],
                ]
            );
            $rewriteTopicParams = [
                CommunicationConfig::TOPIC_IS_SYNCHRONOUS => false,
                CommunicationConfig::TOPIC_RESPONSE       => null,
            ];
            $result[$topicName] = array_merge($topicConfig, $rewriteTopicParams);
        }
        $result[WebApiAsyncConfig::SYSTEM_TOPIC_NAME] = WebApiAsyncConfig::SYSTEM_TOPIC_CONFIGURATION;

        return [CommunicationConfig::TOPICS => $result];
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;
use Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator;

/**
 * Remote service configuration reader.
 */
class Communication implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var ObjectManagerConfig
     */
    private $objectManagerConfig;

    /**
     * @var ReflectionGenerator
     */
    private $dataGenerator;

    /**
     * @var ServiceMethodsMap
     */
    private $serviceMethodsMap;

    /**
     * Initialize dependencies.
     *
     * @param ObjectManagerConfig $objectManagerConfig
     * @param ReflectionGenerator $dataGenerator
     * @param ServiceMethodsMap $serviceMethodsMap
     */
    public function __construct(
        ObjectManagerConfig $objectManagerConfig,
        ReflectionGenerator $dataGenerator,
        ServiceMethodsMap $serviceMethodsMap
    ) {
        $this->objectManagerConfig = $objectManagerConfig;
        $this->dataGenerator = $dataGenerator;
        $this->serviceMethodsMap = $serviceMethodsMap;
    }

    /**
     * Generate communication configuration based on remote services declarations in di.xml
     *
     * @param string|null $scope
     * @return array
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function read($scope = null)
    {
        $preferences = $this->objectManagerConfig->getPreferences();
        $remoteServices = [];
        foreach ($preferences as $type => $preference) {
            if ($preference == $type . RemoteServiceGenerator::REMOTE_SERVICE_SUFFIX) {
                $remoteServices[$type] = $preference;
            }
        }
        $result = [];
        foreach ($remoteServices as $serviceInterface => $remoteImplementation) {
            try {
                $methodsMap = $this->serviceMethodsMap->getMethodsMap($serviceInterface);
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Service interface was expected, "%s" given', $serviceInterface));
            }
            foreach ($methodsMap as $methodName => $returnType) {
                $topicName = $this->dataGenerator->generateTopicName($serviceInterface, $methodName);
                $result[$topicName] = $this->dataGenerator->generateTopicConfigForServiceMethod(
                    $topicName,
                    $serviceInterface,
                    $methodName
                );
                $result[$topicName][CommunicationConfig::TOPIC_HANDLERS] = [];
            }
        }
        return [CommunicationConfig::TOPICS => $result];
    }

    /**
     * Generate topic name based on service type and method name.
     *
     * Perform the following conversion:
     * \Magento\Customer\Api\RepositoryInterface + getById => magento.customer.api.repositoryInterface.getById
     *
     * @param string $typeName
     * @param string $methodName
     * @return string
     *
     * @deprecated 103.0.0
     * @see \Magento\Framework\Communication\Config\ReflectionGenerator::generateTopicName
     */
    public function generateTopicName($typeName, $methodName)
    {
        return $this->dataGenerator->generateTopicName($typeName, $methodName);
    }
}

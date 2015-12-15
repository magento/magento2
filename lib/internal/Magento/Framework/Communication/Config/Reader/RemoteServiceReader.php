<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Magento\Framework\Communication\Config\Reader\XmlReader\Converter as DataGenerator;
use \Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;

/**
 * Remote service configuration reader.
 */
class RemoteServiceReader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var ObjectManagerConfig
     */
    private $objectManagerConfig;

    /**
     * @var DataGenerator
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
     * @param DataGenerator $dataGenerator
     * @param ServiceMethodsMap $serviceMethodsMap
     */
    public function __construct(
        ObjectManagerConfig $objectManagerConfig,
        DataGenerator $dataGenerator,
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
     */
    public function read($scope = null)
    {
        $preferences = $this->objectManagerConfig->getPreferences();
        $remoteServices = array_filter(
            $preferences,
            function ($preferenceTypeName) {
                // TODO: Take this suffix from code generation class constant (when added)
                $remoteServiceSuffix = 'Remote';
                return (substr($preferenceTypeName, -strlen($remoteServiceSuffix)) == $remoteServiceSuffix);
            }
        );
        $result = [];
        foreach ($remoteServices as $serviceInterface => $remoteImplementation) {
            try {
                $methodsMap = $this->serviceMethodsMap->getMethodsMap($serviceInterface);
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Service interface was expected, "%1" given', $serviceInterface));
            }
            foreach ($methodsMap as $methodName => $returnType) {
                // TODO: Add logic based on return type to support async requests
                $topicName = $this->generateTopicName($serviceInterface, $methodName);
                $result[$topicName] = $this->dataGenerator->generateTopicConfigForServiceMethod(
                    $topicName,
                    $serviceInterface,
                    $methodName
                );
            }
        }
        return [CommunicationConfig::TOPICS => $result];
    }

    /**
     * Generate topic name based on service type and method name.
     *
     * Perform the following conversion:
     * \Magento\Customer\Api\RepositoryInterface + getById => Magento.Customer.Api.RepositoryInterface.getById
     *
     * @param string $typeName
     * @param string $methodName
     * @return string
     */
    protected function generateTopicName($typeName, $methodName)
    {
        return ltrim(preg_replace('/\\\\([A-Z])/', '.$1', $typeName), '\\') . '.' . $methodName;
    }
}

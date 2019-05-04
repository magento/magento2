<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\RemoteService;

use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Publisher\Config\ReaderInterface;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;

/**
 * Reader for queue publisher configs based on remote service declaration in DI configs.
 */
class Reader implements ReaderInterface
{
    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * @var ObjectManagerConfig
     */
    private $objectManagerConfig;

    /**
     * @var ReflectionGenerator
     */
    private $reflectionGenerator;

    /**
     * @var ServiceMethodsMap
     */
    private $serviceMethodsMap;

    /**
     * Initialize dependencies.
     *
     * @param DefaultValueProvider $defaultValueProvider
     * @param ObjectManagerConfig $objectManagerConfig
     * @param ReflectionGenerator $reflectionGenerator
     * @param ServiceMethodsMap $serviceMethodsMap
     */
    public function __construct(
        DefaultValueProvider $defaultValueProvider,
        ObjectManagerConfig $objectManagerConfig,
        ReflectionGenerator $reflectionGenerator,
        ServiceMethodsMap $serviceMethodsMap
    ) {
        $this->defaultValueProvider = $defaultValueProvider;
        $this->objectManagerConfig = $objectManagerConfig;
        $this->reflectionGenerator = $reflectionGenerator;
        $this->serviceMethodsMap = $serviceMethodsMap;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function read($scope = null)
    {
        $result = [];
        $connectionName = $this->defaultValueProvider->getConnection();
        $connections = [
            $connectionName => [
                'name' => $connectionName,
                'exchange' => $this->defaultValueProvider->getExchange(),
                'disabled' => false,
            ]
        ];
        foreach ($this->getRemoteServices() as $serviceInterface => $remoteImplementation) {
            try {
                $methodsMap = $this->serviceMethodsMap->getMethodsMap($serviceInterface);
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Service interface was expected, "%s" given', $serviceInterface));
            }
            foreach ($methodsMap as $methodName => $returnType) {
                $topic = $this->reflectionGenerator->generateTopicName($serviceInterface, $methodName);
                $result[$topic] = [
                    'topic' => $topic,
                    'disabled' => false,
                    'connections' => $connections,

                ];
            }
        }
        return $result;
    }

    /**
     * Get list of remote services declared in DI config.
     *
     * @return array
     */
    private function getRemoteServices()
    {
        $preferences = $this->objectManagerConfig->getPreferences();
        $remoteServices = [];
        foreach ($preferences as $type => $preference) {
            if ($preference == $type . RemoteServiceGenerator::REMOTE_SERVICE_SUFFIX) {
                $remoteServices[$type] = $preference;
            }
        }
        return $remoteServices;
    }
}

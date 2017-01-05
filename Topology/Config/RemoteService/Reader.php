<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\RemoteService;

use Magento\Framework\Communication\Config\ReflectionGenerator;
use Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Topology\Config\ReaderInterface;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfig;
use Magento\Framework\Reflection\MethodsMap as ServiceMethodsMap;

/**
 * Reader for queue topology configs based on remote service declaration in DI configs.
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
     */
    public function read($scope = null)
    {
        $exchangeName = $this->defaultValueProvider->getExchange();
        return [
            $exchangeName => [
                'name' => $exchangeName,
                'type' => 'topic',
                'connection' => $this->defaultValueProvider->getConnection(),
                'durable' => true,
                'autoDelete' => false,
                'internal' => false,
                'bindings' => $this->generateBindings(),
                'arguments' => [],
            ]
        ];
    }

    /**
     * Generate list of bindings based on information about remote services declared in DI config.
     *
     * @return array
     *
     * @throws \LogicException
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function generateBindings()
    {
        $bindings = [];
        foreach ($this->getRemoteServices() as $serviceInterface => $remoteImplementation) {
            try {
                $methodsMap = $this->serviceMethodsMap->getMethodsMap($serviceInterface);
            } catch (\Exception $e) {
                throw new \LogicException(sprintf('Service interface was expected, "%s" given', $serviceInterface));
            }
            foreach ($methodsMap as $methodName => $returnType) {
                $topic = $this->reflectionGenerator->generateTopicName($serviceInterface, $methodName);
                $exchangeName = $this->defaultValueProvider->getExchange();
                $destination = 'queue.' . $topic;
                $id = $topic . '--' . $exchangeName . '--' . $destination;
                $bindings[$id] = [
                    'id' => $id,
                    'destinationType' => 'queue',
                    'destination' => $destination,
                    'disabled' => false,
                    'topic' => $topic,
                    'arguments' => []
                ];
            }
        }
        return $bindings;
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

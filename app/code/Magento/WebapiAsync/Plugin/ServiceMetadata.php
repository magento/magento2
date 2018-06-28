<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin;

use Magento\Webapi\Model\Config\Converter as WebapiConverter;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;

class ServiceMetadata
{
    /**
     * @var \Magento\Webapi\Model\Config
     */
    private $webapiConfig;
    /**
     * @var \Magento\WebapiAsync\Model\ServiceConfig
     */
    private $serviceConfig;
    /**
     * @var AsynchronousSchemaRequestProcessor
     */
    private $asynchronousSchemaRequestProcessor;
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    private $typeProcessor;
    /**
     * @var array
     */
    private $responseDefinitionReplacement;
    /**
     * @var array
     */
    private $synchronousOnlyHttpMethods = [
        'GET'
    ];

    /**
     * ServiceMetadata constructor.
     *
     * @param \Magento\Webapi\Model\Config $webapiConfig
     * @param \Magento\WebapiAsync\Model\ServiceConfig $serviceConfig
     * @param AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor
     */
    public function __construct(
        \Magento\Webapi\Model\Config $webapiConfig,
        \Magento\WebapiAsync\Model\ServiceConfig $serviceConfig,
        \Magento\Framework\Webapi\Rest\Request $request,
        AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor
    ) {
        $this->webapiConfig = $webapiConfig;
        $this->serviceConfig = $serviceConfig;
        $this->request = $request;
        $this->asynchronousSchemaRequestProcessor = $asynchronousSchemaRequestProcessor;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * @param \Magento\Webapi\Model\ServiceMetadata $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetServicesConfig(\Magento\Webapi\Model\ServiceMetadata $subject, array $result)
    {
        if ($this->asynchronousSchemaRequestProcessor->canProcess($this->request)) {
            $synchronousOnlyServiceMethods = $this->getSynchronousOnlyServiceMethods($subject);
            // Replace all results with the async response schema
            foreach ($result as $serviceName => $serviceData) {
                // Check all of the methods on the service
                foreach ($serviceData[WebapiConverter::KEY_METHODS] as $methodName => $methodData) {
                    // Exclude service methods that are marked as synchronous only
                    if ($this->isServiceMethodSynchronousOnly(
                        $serviceName,
                        $methodName,
                        $synchronousOnlyServiceMethods
                    )) {
                        $this->removeServiceMethodDefinition($result, $serviceName, $methodName);
                    } else {
                        $this->replaceResponseDefinition($result, $serviceName, $methodName);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param array $synchronousOnlyServiceMethods
     * @return bool
     */
    private function isServiceMethodSynchronousOnly($serviceName, $methodName, array $synchronousOnlyServiceMethods)
    {
        return isset($synchronousOnlyServiceMethods[$serviceName][$methodName]);
    }

    /**
     * @param string $serviceName
     * @return array
     */
    private function getServiceVersions(string $serviceName)
    {
        $services = $this->webapiConfig->getServices();

        return array_keys($services[WebapiConverter::KEY_SERVICES][$serviceName]);
    }

    /**
     * Get a list of all service methods that cannot be executed asynchronously.
     *
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @return array
     */
    private function getSynchronousOnlyServiceMethods(\Magento\Webapi\Model\ServiceMetadata $serviceMetadata)
    {
        $synchronousOnlyServiceMethods = [];
        $services = $this->serviceConfig->getServices()[Converter::KEY_SERVICES] ?? [];
        foreach ($services as $service => $serviceData) {
            if (!isset($serviceData[Converter::KEY_METHODS])) {
                continue;
            }

            foreach ($serviceData[Converter::KEY_METHODS] as $method => $methodData) {
                if ($this->isMethodDataSynchronousOnly($methodData)) {
                    $this->appendSynchronousOnlyServiceMethodsWithInterface(
                        $serviceMetadata,
                        $synchronousOnlyServiceMethods,
                        $service,
                        $method
                    );
                }
            }
        }

        return array_merge_recursive(
            $synchronousOnlyServiceMethods,
            $this->getSynchronousOnlyRoutesAsServiceMethods($serviceMetadata)
        );
    }

    /**
     * Get service methods associated with routes that can't be processed as asynchronous.
     *
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getSynchronousOnlyRoutesAsServiceMethods(
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
    ) {
        $synchronousOnlyServiceMethods = [];
        $serviceRoutes = $this->webapiConfig->getServices()[\Magento\Webapi\Model\Config\Converter::KEY_ROUTES];
        foreach ($serviceRoutes as $serviceRoutePath => $serviceRouteMethods) {
            foreach ($serviceRouteMethods as $serviceRouteMethod => $serviceRouteMethodData) {
                // Check if the HTTP method associated with the route is not able to be async.
                if (in_array(strtoupper($serviceRouteMethod), $this->synchronousOnlyHttpMethods)) {
                    $this->appendSynchronousOnlyServiceMethodsWithInterface(
                        $serviceMetadata,
                        $synchronousOnlyServiceMethods,
                        $serviceRouteMethodData[WebapiConverter::KEY_SERVICE][WebapiConverter::KEY_SERVICE_CLASS],
                        $serviceRouteMethodData[WebapiConverter::KEY_SERVICE][WebapiConverter::KEY_SERVICE_METHOD]
                    );
                }
            }
        }

        return $synchronousOnlyServiceMethods;
    }

    /**
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @param array $synchronousOnlyServiceMethods
     * @param $serviceInterface
     * @param $serviceMethod
     */
    private function appendSynchronousOnlyServiceMethodsWithInterface(
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata,
        array &$synchronousOnlyServiceMethods,
        $serviceInterface,
        $serviceMethod
    ) {
        foreach ($this->getServiceVersions($serviceInterface) as $serviceVersion) {
            $serviceName = $serviceMetadata->getServiceName($serviceInterface, $serviceVersion);
            if (!array_key_exists($serviceName, $synchronousOnlyServiceMethods)) {
                $synchronousOnlyServiceMethods[$serviceName] = [];
            }

            $synchronousOnlyServiceMethods[$serviceName][$serviceMethod] = true;
        }
    }

    /**
     * @param array $result
     * @param $serviceName
     * @param $methodName
     */
    private function removeServiceMethodDefinition(array &$result, $serviceName, $methodName)
    {
        unset($result[$serviceName][WebapiConverter::KEY_METHODS][$methodName]);

        // Remove the service altogether if there is no methods left.
        if (count($result[$serviceName][WebapiConverter::KEY_METHODS]) === 0) {
            unset($result[$serviceName]);
        }
    }

    /**
     * @param array $result
     * @param $serviceName
     * @param $methodName
     */
    private function replaceResponseDefinition(array &$result, $serviceName, $methodName)
    {
        if (isset($result[$serviceName][WebapiConverter::KEY_METHODS][$methodName]['interface']['out'])) {
            $replacement = $this->getResponseDefinitionReplacement();
            $result[$serviceName][WebapiConverter::KEY_METHODS][$methodName]['interface']['out'] = $replacement;
        }
    }

    /**
     * Check if a method on the given service is defined as synchronous only using XML.
     *
     * @param array $methodData
     * @return bool
     */
    private function isMethodDataSynchronousOnly(array $methodData)
    {
        if (!isset($methodData[Converter::KEY_SYNCHRONOUS_INVOCATION_ONLY])) {
            return false;
        }

        return $methodData[Converter::KEY_SYNCHRONOUS_INVOCATION_ONLY];
    }

    /**
     * @return array
     */
    private function getResponseDefinitionReplacement()
    {
        if ($this->responseDefinitionReplacement === null) {
            $this->responseDefinitionReplacement = [
                'parameters' => [
                    'result' => [
                        'type' => $this->typeProcessor->register(AsyncResponseInterface::class),
                        'documentation' => 'Returns response information for the asynchronous request.',
                        'required' => true,
                        'response_codes' => [
                            'success' => [
                                'code' => '202',
                                'description' => '202 Accepted.'
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $this->responseDefinitionReplacement;
    }
}

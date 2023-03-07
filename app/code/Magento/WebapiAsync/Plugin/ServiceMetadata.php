<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin;

use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Model\Config;
use Magento\Webapi\Model\Config\Converter as WebapiConverter;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\Webapi\Model\ServiceMetadata as ModelServiceMetadata;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\WebapiAsync\Model\ServiceConfig;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;

class ServiceMetadata
{
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
     * @param Config $webapiConfig
     * @param ServiceConfig $serviceConfig
     * @param Request $request
     * @param AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        private readonly Config $webapiConfig,
        private readonly ServiceConfig $serviceConfig,
        private readonly Request $request,
        private readonly AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor,
        private readonly TypeProcessor $typeProcessor
    ) {
    }

    /**
     * @param ModelServiceMetadata $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterGetServicesConfig(ModelServiceMetadata $subject, array $result)
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
     * @param ModelServiceMetadata $serviceMetadata
     * @return array
     */
    private function getSynchronousOnlyServiceMethods(ModelServiceMetadata $serviceMetadata)
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
     * @param ModelServiceMetadata $serviceMetadata
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function getSynchronousOnlyRoutesAsServiceMethods(
        ModelServiceMetadata $serviceMetadata
    ) {
        $synchronousOnlyServiceMethods = [];
        $serviceRoutes = $this->webapiConfig->getServices()[WebapiConverter::KEY_ROUTES];
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
     * @param ModelServiceMetadata $serviceMetadata
     * @param array $synchronousOnlyServiceMethods
     * @param $serviceInterface
     * @param $serviceMethod
     */
    private function appendSynchronousOnlyServiceMethodsWithInterface(
        ModelServiceMetadata $serviceMetadata,
        array                &$synchronousOnlyServiceMethods,
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

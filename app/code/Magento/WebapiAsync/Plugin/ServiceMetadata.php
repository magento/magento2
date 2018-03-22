<?php

namespace Magento\WebapiAsync\Plugin;

use Magento\Webapi\Model\Config\Converter as WebapiConverter;
use Magento\WebapiAsync\Api\Data\AsyncResponseInterface;
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
                    if (!$this->isServiceMethodSynchronousOnly(
                        $serviceName,
                        $methodName,
                        $synchronousOnlyServiceMethods
                    )) {
                        $result = $this->replaceResponseDefinition($result, $serviceName, $methodName);
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
     * @return array
     */
    private function getServiceVersions()
    {
        $services = $this->webapiConfig->getServices();
        $serviceVersionData = array_values($services[WebapiConverter::KEY_SERVICES]);

        return array_keys($serviceVersionData);
    }

    /**
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @return array
     */
    private function getSynchronousOnlyServiceMethods(\Magento\Webapi\Model\ServiceMetadata $serviceMetadata)
    {
        $synchronousOnlyServiceMethods = [];
        foreach ($this->serviceConfig->getServices() as $service => $serviceData) {
            if (!isset($serviceData[Converter::KEY_METHODS])) {
                continue;
            }

            foreach ($serviceData[Converter::KEY_METHODS] as $method => $methodData) {
                if ($this->isMethodDataSynchronousOnly($methodData)) {
                    foreach ($this->getServiceVersions() as $serviceVersion) {
                        $serviceName = $serviceMetadata->getServiceName($service, $serviceVersion);
                        if (!array_key_exists($serviceName, $synchronousOnlyServiceMethods)) {
                            $synchronousOnlyServiceMethods[$serviceName] = [];
                        }

                        $synchronousOnlyServiceMethods[$serviceName][$method] = true;
                    }
                }
            }
        }

        return $synchronousOnlyServiceMethods;
    }

    /**
     * @param array $result
     * @param $serviceName
     * @param $methodName
     * @return array
     */
    private function replaceResponseDefinition(array $result, $serviceName, $methodName)
    {
        if (!isset($result[$serviceName][WebapiConverter::KEY_METHODS][$methodName]['interface']['out'])) {
            return $result;
        }

        $replacement = $this->getResponseDefinitionReplacement();
        $result[$serviceName][WebapiConverter::KEY_METHODS][$methodName]['interface']['out'] = $replacement;

        return $result;
    }

    /**
     * Check if a method on the given service is defined as synchronous only.
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
                        'documentation' => 'Returns a 202 Accepted Response when successfully queued.',
                        'required' => true,
                    ]
                ]
            ];
        }

        return $this->responseDefinitionReplacement;
    }
}

<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model;

use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\Cache\Type\Webapi;

/**
 * Abstract API schema generator.
 */
abstract class AbstractSchemaGenerator
{
    /**
     * @var Webapi
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface
     */
    protected $customAttributeTypeLocator;

     /**
      * @var \Magento\Webapi\Model\ServiceMetadata
      */
    protected $serviceMetadata;

    /**
     * Initialize dependencies.
     *
     * @param Webapi $cache
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     */
    public function __construct(
        Webapi $cache,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
    ) {
        $this->cache = $cache;
        $this->typeProcessor = $typeProcessor;
        $this->storeManager = $storeManager;
        $this->customAttributeTypeLocator = $customAttributeTypeLocator;
        $this->serviceMetadata = $serviceMetadata;
    }

    /**
     * Retrieve an array of services
     *
     * @return array
     */
    public function getListOfServices()
    {
        return $this->serviceMetadata->getServicesConfig();
    }

    /**
     * Generate schema based on requested services (uses cache)
     *
     * @param array $requestedServices
     * @param string $requestScheme
     * @param string $requestHost
     * @param string $endPointUrl
     * @return string
     */
    public function generate($requestedServices, $requestScheme, $requestHost, $endPointUrl)
    {
        /** Sort requested services by names to prevent caching of the same schema file more than once. */
        ksort($requestedServices);
        $currentStore = $this->storeManager->getStore();
        $cacheId = get_class($this). hash('md5', serialize($requestedServices) . $currentStore->getCode());
        $cachedSchemaContent = $this->cache->load($cacheId);
        if ($cachedSchemaContent !== false) {
            return $cachedSchemaContent;
        }
        $requestedServiceMetadata = [];
        foreach ($requestedServices as $serviceName) {
            $requestedServiceMetadata[$serviceName] = $this->getServiceMetadata($serviceName);
        }

        $this->collectCallInfo($requestedServiceMetadata);
        $schemaContent = $this->generateSchema($requestedServiceMetadata, $requestScheme, $requestHost, $endPointUrl);
        $this->cache->save($schemaContent, $cacheId, [Webapi::CACHE_TAG]);

        return $schemaContent;
    }

    /**
     * Generate schema based on requested services' metadata.
     *
     * @param array $requestedServiceMetadata
     * @param string $requestScheme
     * @param string $requestHost
     * @param string $requestUri
     * @return string
     */
    abstract protected function generateSchema($requestedServiceMetadata, $requestScheme, $requestHost, $requestUri);

    /**
     * Get service metadata
     *
     * @param string $serviceName
     * @return string[]
     */
    abstract protected function getServiceMetadata($serviceName);

    /**
     * Get name of complexType for message element.
     *
     * @param string $messageName
     * @return string
     */
    public function getElementComplexTypeName($messageName)
    {
        return ucfirst($messageName);
    }

    /**
     * Collect data about complex types call info.
     *
     * Walks through all requested services and checks all methods 'in' and 'out' parameters.
     *
     * @param array $requestedServiceMetadata
     * @return void
     */
    protected function collectCallInfo($requestedServiceMetadata)
    {
        foreach ($requestedServiceMetadata as $serviceName => $serviceData) {
            foreach ($serviceData['methods'] as $methodName => $methodData) {
                $this->typeProcessor->processInterfaceCallInfo($methodData['interface'], $serviceName, $methodName);
            }
        }
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Authorization;
use Magento\Webapi\Model\Cache\Type\Webapi;

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
     * @var \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface
     */
    protected $customAttributeTypeLocator;

     /**
      * @var ServiceMetadata
      */
    protected $serviceMetadata;

    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * Instance of serializer.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param Webapi $cache
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @param Authorization $authorization
     * @param Json|null $serializer
     */
    public function __construct(
        Webapi $cache,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        ServiceMetadata $serviceMetadata,
        Authorization $authorization,
        Json $serializer = null
    ) {
        $this->cache = $cache;
        $this->typeProcessor = $typeProcessor;
        $this->customAttributeTypeLocator = $customAttributeTypeLocator;
        $this->serviceMetadata = $serviceMetadata;
        $this->authorization = $authorization;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Retrieve a list of all services.
     *
     * @return string[]
     */
    public function getListOfServices()
    {
        return array_keys($this->serviceMetadata->getServicesConfig());
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
        $prefix = get_class($this) . $this->serializer->serialize($requestedServices);
        $cacheId = $this->cache->generateCacheIdUsingContext($prefix);
        $cachedSchemaContent = $this->cache->load($cacheId);
        if ($cachedSchemaContent !== false) {
            return $cachedSchemaContent;
        }
        $allowedServicesMetadata = $this->getAllowedServicesMetadata($requestedServices);
        $this->collectCallInfo($allowedServicesMetadata);
        $schemaContent = $this->generateSchema($allowedServicesMetadata, $requestScheme, $requestHost, $endPointUrl);
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
     * @return array
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

    /**
     * Retrieve information only about those services/methods which are visible to current user.
     *
     * @param string[] $requestedServices
     * @return array
     */
    protected function getAllowedServicesMetadata($requestedServices)
    {
        $allowedServicesMetadata = [];
        foreach ($requestedServices as $serviceName) {
            $serviceMetadata = $this->getServiceMetadata($serviceName);
            foreach ($serviceMetadata[ServiceMetadata::KEY_SERVICE_METHODS] as $methodName => $methodData) {
                if (!$this->authorization->isAllowed($methodData[ServiceMetadata::KEY_ACL_RESOURCES])) {
                    unset($serviceMetadata[ServiceMetadata::KEY_SERVICE_METHODS][$methodName]);
                }
            }
            if (!empty($serviceMetadata[ServiceMetadata::KEY_SERVICE_METHODS])) {
                $this->removeRestrictedRoutes($serviceMetadata);
                $allowedServicesMetadata[$serviceName] = $serviceMetadata;
            }
        }
        return $allowedServicesMetadata;
    }

    /**
     * Remove routes which should not be visible to current user.
     *
     * @param array &$serviceMetadata
     * @return void
     */
    protected function removeRestrictedRoutes(&$serviceMetadata)
    {
        $allowedMethodNames = array_keys($serviceMetadata[ServiceMetadata::KEY_SERVICE_METHODS]);
        /** Remove routes which reference methods not visible to current user */
        if (isset($serviceMetadata[ServiceMetadata::KEY_ROUTES])) {
            foreach ($serviceMetadata[ServiceMetadata::KEY_ROUTES] as $path => &$routeGroup) {
                foreach ($routeGroup as $httpMethod => &$route) {
                    if (!in_array($route[ServiceMetadata::KEY_ROUTE_METHOD], $allowedMethodNames)) {
                        unset($routeGroup[$httpMethod]);
                    }
                }
                if (empty($routeGroup)) {
                    unset($serviceMetadata[ServiceMetadata::KEY_ROUTES][$path]);
                }
            }
        }
    }
}

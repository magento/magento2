<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Model;

use InvalidArgumentException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebApiCache;
use Magento\Webapi\Model\Config\ClassReflector;
use Magento\Webapi\Model\Config\Converter;
use RuntimeException;

/**
 * Service Metadata Model
 */
class ServiceMetadata
{
    /**#@+
     * Keys that a used for service config internal representation.
     */
    public const KEY_CLASS = 'class';

    public const KEY_IS_SECURE = 'isSecure';

    public const KEY_SERVICE_METHODS = 'methods';

    public const KEY_METHOD = 'method';

    public const KEY_IS_REQUIRED = 'inputRequired';

    public const KEY_ACL_RESOURCES = 'resources';

    public const KEY_ROUTES = 'routes';

    public const KEY_ROUTE_METHOD = 'method';

    public const KEY_ROUTE_PARAMS = 'parameters';

    public const KEY_METHOD_ALIAS = 'methodAlias';

    public const KEY_INPUT_ARRAY_SIZE_LIMIT = 'input-array-size-limit';

    public const SERVICES_CONFIG_CACHE_ID = 'services-services-config';

    public const ROUTES_CONFIG_CACHE_ID = 'routes-services-config';

    public const REFLECTED_TYPES_CACHE_ID = 'soap-reflected-types';

    /**#@-*/

    /**
     * @var array
     */
    protected $services;

    /**
     * List of services with route data
     *
     * @var array
     */
    protected $routes;

    /**
     * @var WebApiCache
     */
    protected $cache;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ClassReflector
     */
    protected $classReflector;

    /**
     * @var TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param WebApiCache $cache
     * @param ClassReflector $classReflector
     * @param TypeProcessor $typeProcessor
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Config $config,
        WebApiCache $cache,
        ClassReflector $classReflector,
        TypeProcessor $typeProcessor,
        SerializerInterface $serializer = null
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->classReflector = $classReflector;
        $this->typeProcessor = $typeProcessor;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Collect the list of services metadata
     *
     * @return array
     */
    protected function initServicesMetadata()
    {
        $services = [];
        foreach ($this->config->getServices()[Converter::KEY_SERVICES] as $serviceClass => $serviceVersionData) {
            foreach ($serviceVersionData as $version => $serviceData) {
                $serviceName = $this->getServiceName($serviceClass, $version);
                $methods = [];
                foreach ($serviceData[Converter::KEY_METHODS] as $methodName => $methodMetadata) {
                    $services[$serviceName][self::KEY_SERVICE_METHODS][$methodName] = [
                        self::KEY_METHOD => $methodMetadata[Converter::KEY_REAL_SERVICE_METHOD],
                        self::KEY_IS_REQUIRED => (bool)$methodMetadata[Converter::KEY_SECURE],
                        self::KEY_IS_SECURE => $methodMetadata[Converter::KEY_SECURE],
                        self::KEY_ACL_RESOURCES => $methodMetadata[Converter::KEY_ACL_RESOURCES],
                        self::KEY_METHOD_ALIAS => $methodName,
                        self::KEY_ROUTE_PARAMS => $methodMetadata[Converter::KEY_DATA_PARAMETERS],
                        self::KEY_INPUT_ARRAY_SIZE_LIMIT => $methodMetadata[Converter::KEY_INPUT_ARRAY_SIZE_LIMIT],
                    ];
                    $services[$serviceName][self::KEY_CLASS] = $serviceClass;
                    $methods[] = $methodMetadata[Converter::KEY_REAL_SERVICE_METHOD];
                }
                unset($methodName, $methodMetadata);
                $reflectedMethodsMetadata = $this->classReflector->reflectClassMethods(
                    $serviceClass,
                    $methods
                );
                foreach ($services[$serviceName][self::KEY_SERVICE_METHODS] as $methodName => &$methodMetadata) {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $methodMetadata = array_merge(
                        $methodMetadata,
                        $reflectedMethodsMetadata[$methodMetadata[self::KEY_METHOD]]
                    );
                }
                unset($methodName, $methodMetadata);
                $services[$serviceName][Converter::KEY_DESCRIPTION] = $this->classReflector->extractClassDescription(
                    $serviceClass
                );
            }
        }

        return $services;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServicesConfig()
    {
        if (null === $this->services) {
            $servicesConfig = $this->cache->load(self::SERVICES_CONFIG_CACHE_ID);
            $typesData = $this->cache->load(self::REFLECTED_TYPES_CACHE_ID);
            if ($servicesConfig && is_string($servicesConfig) && $typesData && is_string($typesData)) {
                $this->services = $this->serializer->unserialize($servicesConfig);
                $this->typeProcessor->setTypesData($this->serializer->unserialize($typesData));
            } else {
                $this->services = $this->initServicesMetadata();
                $this->cache->save(
                    $this->serializer->serialize($this->services),
                    self::SERVICES_CONFIG_CACHE_ID
                );
                $this->cache->save(
                    $this->serializer->serialize($this->typeProcessor->getTypesData()),
                    self::REFLECTED_TYPES_CACHE_ID
                );
            }
        }
        return $this->services;
    }

    /**
     * Retrieve specific service interface data.
     *
     * @param string $serviceName
     * @return array
     * @throws RuntimeException
     */
    public function getServiceMetadata($serviceName)
    {
        $servicesConfig = $this->getServicesConfig();
        if (!isset($servicesConfig[$serviceName]) || !is_array($servicesConfig[$serviceName])) {
            throw new RuntimeException(__('Requested service is not available: "%1"', $serviceName)->render());
        }
        return $servicesConfig[$serviceName];
    }

    /**
     * Translate service interface name into service name.
     *
     * Example:
     * <pre>
     * - \Magento\Customer\Api\CustomerAccountInterface::class, 'V1', false => customerCustomerAccount
     * - \Magento\Customer\Api\CustomerAddressInterface::class, 'V1', true  => customerCustomerAddressV1
     * </pre>
     *
     * @param string $interfaceName
     * @param string $version
     * @param bool $preserveVersion Should version be preserved during interface name conversion into service name
     * @return string
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getServiceName($interfaceName, $version, $preserveVersion = true)
    {
        if ($interfaceName && !preg_match(Config::SERVICE_CLASS_PATTERN, $interfaceName, $matches)) {
            $apiClassPattern = "#^(.+?)\\\\(.+?)\\\\Api\\\\(.+?)(Interface)?$#";
            preg_match($apiClassPattern, $interfaceName, $matches);
        }

        if (!empty($matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
            if ($matches[4] === 'Interface') {
                $matches[4] = $matches[3];
            }
            $serviceNameParts = explode('\\', trim($matches[4] ?? '', '\\'));
            if ($moduleName == $serviceNameParts[0]) {
                /** Avoid duplication of words in service name */
                $moduleName = '';
            }
            $parentServiceName = $moduleNamespace . $moduleName . array_shift($serviceNameParts);
            array_unshift($serviceNameParts, $parentServiceName);
            if ($preserveVersion) {
                $serviceNameParts[] = $version;
            }
        } elseif ($interfaceName && preg_match(Config::API_PATTERN, $interfaceName, $matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
            $serviceNameParts = explode('\\', trim($matches[3] ?? '', '\\'));
            if ($moduleName == $serviceNameParts[0]) {
                /** Avoid duplication of words in service name */
                $moduleName = '';
            }
            $parentServiceName = $moduleNamespace . $moduleName . array_shift($serviceNameParts);
            array_unshift($serviceNameParts, $parentServiceName);
            if ($preserveVersion) {
                $serviceNameParts[] = $version;
            }
        } else {
            throw new InvalidArgumentException(sprintf('The service interface name "%s" is invalid.', $interfaceName));
        }
        return lcfirst(implode('', $serviceNameParts));
    }

    /**
     * Retrieve specific service interface data with route.
     *
     * @param string $serviceName
     * @return array
     * @throws RuntimeException
     */
    public function getRouteMetadata($serviceName)
    {
        $routesConfig = $this->getRoutesConfig();
        if (!isset($routesConfig[$serviceName]) || !is_array($routesConfig[$serviceName])) {
            throw new RuntimeException(__('Requested service is not available: "%1"', $serviceName)->render());
        }
        return $routesConfig[$serviceName];
    }

    /**
     * Return routes loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getRoutesConfig()
    {
        if (null === $this->routes) {
            $routesConfig = $this->cache->load(self::ROUTES_CONFIG_CACHE_ID);
            $typesData = $this->cache->load(self::REFLECTED_TYPES_CACHE_ID);
            if ($routesConfig && is_string($routesConfig) && $typesData && is_string($typesData)) {
                $this->routes = $this->serializer->unserialize($routesConfig);
                $this->typeProcessor->setTypesData($this->serializer->unserialize($typesData));
            } else {
                $this->routes = $this->initRoutesMetadata();
                $this->cache->save(
                    $this->serializer->serialize($this->routes),
                    self::ROUTES_CONFIG_CACHE_ID
                );
                $this->cache->save(
                    $this->serializer->serialize($this->typeProcessor->getTypesData()),
                    self::REFLECTED_TYPES_CACHE_ID
                );
            }
        }
        return $this->routes;
    }

    /**
     * Collect the list of services with routes and request types for use in REST.
     *
     * @return array
     */
    protected function initRoutesMetadata()
    {
        $routes = $this->getServicesConfig();
        foreach ($this->config->getServices()[Converter::KEY_ROUTES] as $url => $routeData) {
            foreach ($routeData as $method => $data) {
                $serviceClass = $data[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS];
                $version = explode('/', ltrim($url, '/'))[0];
                $serviceName = $this->getServiceName($serviceClass, $version);
                $methodName = $data[Converter::KEY_SERVICE][Converter::KEY_METHOD];
                $limit = $data[Converter::KEY_INPUT_ARRAY_SIZE_LIMIT];
                $routes[$serviceName][self::KEY_ROUTES][$url][$method][self::KEY_ROUTE_METHOD] = $methodName;
                $routes[$serviceName][self::KEY_ROUTES][$url][$method][self::KEY_ROUTE_PARAMS]
                    = $data[Converter::KEY_DATA_PARAMETERS];
                $routes[$serviceName][self::KEY_ROUTES][$url][$method][self::KEY_INPUT_ARRAY_SIZE_LIMIT] = $limit;
            }
        }
        return $routes;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\AsynchronousOperations\Model\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config as WebapiConfig;
use Magento\Webapi\Model\Config\Converter;

/**
 * Class for accessing to Webapi_Async configuration.
 */
class Config implements ConfigInterface
{
    /**
     * @var WebapiCache
     */
    private $cache;

    /**
     * @var WebapiConfig
     */
    private $webApiConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $asyncServices;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param WebapiConfig $webApiConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        WebapiCache $cache,
        WebapiConfig $webApiConfig,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->webApiConfig = $webApiConfig;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function getServices()
    {
        if (null === $this->asyncServices) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->asyncServices = $this->serializer->unserialize($services);
            } else {
                $this->asyncServices = $this->generateTopicsDataFromWebapiConfig();
                $this->cache->save($this->serializer->serialize($this->asyncServices), self::CACHE_ID);
            }
        }

        return $this->asyncServices;
    }

    /**
     * @inheritdoc
     */
    public function getTopicName($routeUrl, $httpMethod)
    {
        $services = $this->getServices();
        $lookupKey = $this->generateLookupKeyByRouteData(
            $routeUrl,
            $httpMethod
        );

        if (array_key_exists($lookupKey, $services) === false) {
            throw new LocalizedException(
                __('WebapiAsync config for "%lookupKey" does not exist.', ['lookupKey' => $lookupKey])
            );
        }

        return $services[$lookupKey][self::SERVICE_PARAM_KEY_TOPIC];
    }

    /**
     * Generate topic data for all defined services
     *
     * Topic data is indexed by a lookup key that is derived from route data
     *
     * @return array
     */
    private function generateTopicsDataFromWebapiConfig()
    {
        $webApiConfig = $this->webApiConfig->getServices();
        $services = [];
        foreach ($webApiConfig[Converter::KEY_ROUTES] as $routeUrl => $routeData) {
            foreach ($routeData as $httpMethod => $httpMethodData) {
                if ($httpMethod !== \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET) {
                    $serviceInterface = $httpMethodData[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS];
                    $serviceMethod = $httpMethodData[Converter::KEY_SERVICE][Converter::KEY_SERVICE_METHOD];

                    $lookupKey = $this->generateLookupKeyByRouteData(
                        $routeUrl,
                        $httpMethod
                    );

                    $topicName = $this->generateTopicNameFromService(
                        $serviceInterface,
                        $serviceMethod,
                        $httpMethod
                    );

                    $services[$lookupKey] = [
                        self::SERVICE_PARAM_KEY_INTERFACE => $serviceInterface,
                        self::SERVICE_PARAM_KEY_METHOD    => $serviceMethod,
                        self::SERVICE_PARAM_KEY_TOPIC     => $topicName,
                    ];
                }
            }
        }

        return $services;
    }

    /**
     * Generate lookup key name based on route and method
     *
     * Perform the following conversion:
     * self::TOPIC_PREFIX + /V1/products + POST => async.V1.products.POST
     *
     * @param string $routeUrl
     * @param string $httpMethod
     * @return string
     */
    private function generateLookupKeyByRouteData($routeUrl, $httpMethod)
    {
        return self::TOPIC_PREFIX . $this->generateKey($routeUrl, $httpMethod, '/', false);
    }

    /**
     * Generate topic name based on service type and method name.
     *
     * Perform the following conversion:
     * self::TOPIC_PREFIX + Magento\Catalog\Api\ProductRepositoryInterface + save + POST
     *   => async.magento.catalog.api.productrepositoryinterface.save.POST
     *
     * @param string $serviceInterface
     * @param string $serviceMethod
     * @param string $httpMethod
     * @return string
     */
    private function generateTopicNameFromService($serviceInterface, $serviceMethod, $httpMethod)
    {
        $typeName = strtolower(sprintf('%s.%s', $serviceInterface, $serviceMethod));
        return strtolower(self::TOPIC_PREFIX . $this->generateKey($typeName, $httpMethod, '\\', false));
    }

    /**
     * Join and simplify input type and method into a string that can be used as an array key
     *
     * @param string $typeName
     * @param string $methodName
     * @param string $delimiter
     * @param bool $lcfirst
     * @return string
     */
    private function generateKey($typeName, $methodName, $delimiter = '\\', $lcfirst = true)
    {
        $parts = explode($delimiter, trim($typeName, $delimiter));
        foreach ($parts as &$part) {
            $part = ltrim($part, ':');
            if ($lcfirst === true) {
                $part = lcfirst($part);
            }
        }

        return implode('.', $parts) . '.' . $methodName;
    }
}

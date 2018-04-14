<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config as WebapiConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Webapi\Model\Config\Converter;

class Config implements \Magento\AsynchronousOperations\Model\ConfigInterface
{
    /**
     * @var \Magento\Webapi\Model\Cache\Type\Webapi
     */
    private $cache;

    /**
     * @var \Magento\Webapi\Model\Config
     */
    private $webApiConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $asyncServices;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $cache
     * @param \Magento\Webapi\Model\Config $webApiConfig
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     */
    public function __construct(
        WebapiCache $cache,
        WebapiConfig $webApiConfig,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->webApiConfig = $webApiConfig;
        $this->serializer = $serializer ? : ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getTopicName($routeUrl, $httpMethod)
    {
        $services = $this->getServices();
        $topicName = $this->generateTopicNameByRouteData(
            $routeUrl,
            $httpMethod
        );

        if (array_key_exists($topicName, $services) === false) {
            throw new LocalizedException(
                __('WebapiAsync config for "%topicName" does not exist.', ['topicName' => $topicName])
            );
        }

        return $services[$topicName][self::SERVICE_PARAM_KEY_TOPIC];
    }

    /**
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

                    $topicName = $this->generateTopicNameByRouteData(
                        $routeUrl,
                        $httpMethod
                    );
                    $services[$topicName] = [
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
     * Generate topic name based on service type and method name.
     *
     * Perform the following conversion:
     * self::TOPIC_PREFIX + /V1/products + POST => async.V1.products.POST
     *
     * @param string $routeUrl
     * @param string $httpMethod
     * @return string
     */
    private function generateTopicNameByRouteData($routeUrl, $httpMethod)
    {
        return self::TOPIC_PREFIX . $this->generateTopicName($routeUrl, $httpMethod, '/', false);
    }

    /**
     * @param string $typeName
     * @param string $methodName
     * @param string $delimiter
     * @param bool $lcfirst
     * @return string
     */
    private function generateTopicName($typeName, $methodName, $delimiter = '\\', $lcfirst = true)
    {
        $parts = explode($delimiter, ltrim($typeName, $delimiter));
        foreach ($parts as &$part) {
            $part = ltrim($part, ':');
            if ($lcfirst === true) {
                $part = lcfirst($part);
            }
        }

        return implode('.', $parts) . '.' . $methodName;
    }
}

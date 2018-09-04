<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model\ServiceConfig;

/**
 * Converter of webapi_async.xml content into array format.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_SERVICES = 'services';
    const KEY_METHOD = 'method';
    const KEY_METHODS = 'methods';
    const KEY_SYNCHRONOUS_INVOCATION_ONLY = 'synchronousInvocationOnly';
    const KEY_ROUTES = 'routes';
    /**#@-*/

    private $allowedRouteMethods = [
        \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
        \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_POST,
        \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_PUT,
        \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_DELETE,
        \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_PATCH
    ];

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function convert($source)
    {
        $result = [self::KEY_SERVICES => []];
        /** @var \DOMNodeList $services */
        $services = $source->getElementsByTagName('service');
        /** @var \DOMElement $service */
        foreach ($services as $service) {
            if (!$this->canConvertXmlNode($service)) {
                continue;
            }
            $serviceClass = $this->getServiceClass($service);
            $serviceMethod = $this->getServiceMethod($service);

            // Define the service method's key if this hasn't yet been defined
            $this->initServiceMethodsKey($result, $serviceClass, $serviceMethod);
            $this->mergeSynchronousInvocationMethodsData($service, $result, $serviceClass, $serviceMethod);
        }
        $result[self::KEY_ROUTES] = $this->convertRouteCustomizations($source);

        return $result;
    }

    /**
     * Merge service data related to synchronous-only method invocations.
     *
     * @param \DOMElement $service
     * @param array $result
     * @param string $serviceClass
     * @param string $serviceMethod
     */
    private function mergeSynchronousInvocationMethodsData(
        \DOMElement $service,
        array &$result,
        $serviceClass,
        $serviceMethod
    ) {
        $result[self::KEY_SERVICES][$serviceClass][self::KEY_METHODS][$serviceMethod] = array_merge(
            $result[self::KEY_SERVICES][$serviceClass][self::KEY_METHODS][$serviceMethod],
            [
                self::KEY_SYNCHRONOUS_INVOCATION_ONLY => $this->isSynchronousMethodInvocationOnly($service)
            ]
        );
    }

    /**
     * @param \DOMElement $node
     * @return bool
     */
    private function canConvertXmlNode(\DOMElement $node)
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }

        if ($this->getServiceClass($node) === null) {
            return false;
        }

        if ($this->getServiceMethod($node) === null) {
            return false;
        }

        return true;
    }

    /**
     * Define the methods key against the service. Allows for other types of service information.
     *
     * @param array $result
     * @param string $serviceClass
     * @param string $serviceMethod
     */
    private function initServiceMethodsKey(array &$result, $serviceClass, $serviceMethod)
    {
        if (!isset($result[self::KEY_SERVICES][$serviceClass])) {
            $result[self::KEY_SERVICES][$serviceClass] = [self::KEY_METHODS => []];
        }

        if (!isset($result[self::KEY_SERVICES][$serviceClass][self::KEY_METHODS][$serviceMethod])) {
            $result[self::KEY_SERVICES][$serviceClass][self::KEY_METHODS][$serviceMethod] = [];
        }
    }

    /**
     * @param \DOMElement $service
     * @return null|string
     */
    private function getServiceClass(\DOMElement $service)
    {
        $serviceClass = $service->attributes->getNamedItem('class')->nodeValue;

        return mb_strlen((string) $serviceClass) === 0 ? null : $serviceClass;
    }

    /**
     * @param \DOMElement $service
     * @return null|string
     */
    private function getServiceMethod(\DOMElement $service)
    {
        $serviceMethod = $service->attributes->getNamedItem('method')->nodeValue;

        return mb_strlen((string) $serviceMethod) === 0 ? null : $serviceMethod;
    }

    /**
     * @param \DOMElement $serviceNode
     * @return bool
     */
    private function isSynchronousMethodInvocationOnly(\DOMElement $serviceNode)
    {
        $synchronousInvocationOnlyNodes = $serviceNode->getElementsByTagName('synchronousInvocationOnly');

        return $this->isSynchronousInvocationOnlyTrue($synchronousInvocationOnlyNodes->item(0));
    }

    /**
     * @param \DOMElement $synchronousInvocationOnlyNode
     * @return bool|mixed
     */
    private function isSynchronousInvocationOnlyTrue(\DOMElement $synchronousInvocationOnlyNode = null)
    {
        if ($synchronousInvocationOnlyNode === null) {
            return false;
        }

        if (mb_strlen((string) $synchronousInvocationOnlyNode->nodeValue) === 0) {
            return true;
        }

        return filter_var($synchronousInvocationOnlyNode->nodeValue, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Convert and merge "route" nodes, which represent route customizations
     * @param \DOMDocument $source
     * @return array
     */
    private function convertRouteCustomizations($source)
    {
        $customRoutes = [];
        $routes = $source->getElementsByTagName('route');
        /** @var \DOMElement  $route */
        foreach ($routes as $route) {
            $routeUrl = $this->getRouteUrl($route);
            $routeMethod = $this->getRouteMethod($route);
            $routeAlias = $this->getRouteAlias($route);
            if ($routeUrl && $routeMethod && $routeAlias) {
                if (!isset($customRoutes[$routeAlias])) {
                    $customRoutes[$routeAlias] = [];
                }
                $customRoutes[$routeAlias][$routeMethod] = $routeUrl;
            }
        }
        return $customRoutes;
    }

    /**
     * @param \DOMElement $route
     * @return null|string
     */
    private function getRouteUrl($route)
    {
        $url = $route->attributes->getNamedItem('url')->nodeValue;
        return mb_strlen((string) $url) === 0 ? null : $url;
    }

    /**
     * @param \DOMElement $route
     * @return null|string
     */
    private function getRouteAlias($route)
    {
        $alias = $route->attributes->getNamedItem('alias')->nodeValue;
        return mb_strlen((string) $alias) === 0 ? null : ltrim($alias, '/');
    }

    /**
     * @param \DOMElement $route
     * @return null|string
     */
    private function getRouteMethod($route)
    {
        $method = $route->attributes->getNamedItem('method')->nodeValue;
        $method =  mb_strlen((string) $method) === 0 ? null : $method;
        return ($this->validateRouteMethod($method)) ? $method : null;
    }

    /**
     * @param string $method
     * @return bool
     */
    private function validateRouteMethod($method)
    {
        return in_array($method, $this->allowedRouteMethods);
    }
}

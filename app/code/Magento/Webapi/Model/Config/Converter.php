<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config;

/**
 * Converter of webapi.xml content into array format.
 * @since 2.0.0
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_SERVICE_CLASS = 'class';
    const KEY_URL = 'url';
    const KEY_SERVICE_METHOD = 'method';
    const KEY_SECURE = 'secure';
    const KEY_ROUTES = 'routes';
    const KEY_ACL_RESOURCES = 'resources';
    const KEY_SERVICE = 'service';
    const KEY_SERVICES = 'services';
    const KEY_FORCE = 'force';
    const KEY_VALUE = 'value';
    const KEY_DATA_PARAMETERS = 'parameters';
    const KEY_SOURCE = 'source';
    const KEY_METHOD = 'method';
    const KEY_METHODS = 'methods';
    const KEY_DESCRIPTION = 'description';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNodeList $routes */
        $routes = $source->getElementsByTagName('route');
        /** @var \DOMElement $route */
        foreach ($routes as $route) {
            if ($route->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            /** @var \DOMElement $service */
            $service = $route->getElementsByTagName('service')->item(0);
            $serviceClass = $service->attributes->getNamedItem('class')->nodeValue;
            $serviceMethod = $service->attributes->getNamedItem('method')->nodeValue;
            $url = trim($route->attributes->getNamedItem('url')->nodeValue);
            $version = $this->convertVersion($url);

            $serviceClassData = [];
            if (isset($result[self::KEY_SERVICES][$serviceClass][$version])) {
                $serviceClassData = $result[self::KEY_SERVICES][$serviceClass][$version];
            }

            $resources = $route->getElementsByTagName('resource');
            $resourceReferences = [];
            $resourcePermissionSet = [];
            /** @var \DOMElement $resource */
            foreach ($resources as $resource) {
                if ($resource->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $ref = $resource->attributes->getNamedItem('ref')->nodeValue;
                $resourceReferences[$ref] = true;
                // For SOAP
                $resourcePermissionSet[] = $ref;
            }

            if (!isset($serviceClassData[self::KEY_METHODS][$serviceMethod])) {
                $serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_ACL_RESOURCES] = $resourcePermissionSet;
            } else {
                $serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_ACL_RESOURCES] =
                    array_unique(
                        array_merge(
                            $serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_ACL_RESOURCES],
                            $resourcePermissionSet
                        )
                    );
            }

            $method = $route->attributes->getNamedItem('method')->nodeValue;
            $secureNode = $route->attributes->getNamedItem('secure');
            $secure = $secureNode ? (bool)trim($secureNode->nodeValue) : false;
            $data = $this->convertMethodParameters($route->getElementsByTagName('parameter'));

            // We could handle merging here by checking if the route already exists
            $result[self::KEY_ROUTES][$url][$method] = [
                self::KEY_SECURE => $secure,
                self::KEY_SERVICE => [
                    self::KEY_SERVICE_CLASS => $serviceClass,
                    self::KEY_SERVICE_METHOD => $serviceMethod,
                ],
                self::KEY_ACL_RESOURCES => $resourceReferences,
                self::KEY_DATA_PARAMETERS => $data,
            ];

            $serviceSecure = false;
            if (isset($serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_SECURE])) {
                $serviceSecure = $serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_SECURE];
            }
            $serviceClassData[self::KEY_METHODS][$serviceMethod][self::KEY_SECURE] = $serviceSecure || $secure;

            $result[self::KEY_SERVICES][$serviceClass][$version] = $serviceClassData;
        }
        return $result;
    }

    /**
     * Parses the method parameters into a string array.
     *
     * @param \DOMNodeList $parameters
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function convertMethodParameters($parameters)
    {
        $data = [];
        /** @var \DOMElement $parameter */
        foreach ($parameters as $parameter) {
            if ($parameter->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $name = $parameter->attributes->getNamedItem('name')->nodeValue;
            $forceNode = $parameter->attributes->getNamedItem('force');
            $force = $forceNode ? (bool)$forceNode->nodeValue : false;
            $value = $parameter->nodeValue;
            $data[$name] = [
                self::KEY_FORCE => $force,
                self::KEY_VALUE => ($value === 'null') ? null : $value,
            ];
            $sourceNode = $parameter->attributes->getNamedItem('source');
            if ($sourceNode) {
                $data[$name][self::KEY_SOURCE] = $sourceNode->nodeValue;
            }
            $methodNode = $parameter->attributes->getNamedItem('method');
            if ($methodNode) {
                $data[$name][self::KEY_METHOD] = $methodNode->nodeValue;
            }
        }
        return $data;
    }

    /**
     * Derive the version from the provided URL.
     * Assumes the version is the first portion of the URL. For example, '/V1/customers'
     *
     * @param string $url
     * @return string
     * @since 2.0.0
     */
    protected function convertVersion($url)
    {
        return substr($url, 1, strpos($url, '/', 1)-1);
    }
}

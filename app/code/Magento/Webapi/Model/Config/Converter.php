<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config;

/**
 * Converter of webapi.xml content into array format.
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
    /**#@-*/

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
            if (!isset($result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_ACL_RESOURCES])) {
                $result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_ACL_RESOURCES]
                    = $resourcePermissionSet;
            } else {
                $result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_ACL_RESOURCES] =
                    array_unique(
                        array_merge(
                            $result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_ACL_RESOURCES],
                            $resourcePermissionSet
                        )
                    );
            }

            $parameters = $route->getElementsByTagName('parameter');
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

            $method = $route->attributes->getNamedItem('method')->nodeValue;
            $url = trim($route->attributes->getNamedItem('url')->nodeValue);
            $secureNode = $route->attributes->getNamedItem('secure');
            $secure = $secureNode ? (bool)trim($secureNode->nodeValue) : false;
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
            if (isset($result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_SECURE])) {
                $serviceSecure = $result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_SECURE];
            }
            $result[self::KEY_SERVICES][$serviceClass][$serviceMethod][self::KEY_SECURE] = $serviceSecure || $secure;
        }
        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Model\Config;

/**
 * Converter of webapi.xml content into array format.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    public const KEY_SERVICE_CLASS = 'class';
    public const KEY_URL = 'url';
    public const KEY_SERVICE_METHOD = 'method';
    public const KEY_SECURE = 'secure';
    public const KEY_ROUTES = 'routes';
    public const KEY_ACL_RESOURCES = 'resources';
    public const KEY_SERVICE = 'service';
    public const KEY_SERVICES = 'services';
    public const KEY_FORCE = 'force';
    public const KEY_VALUE = 'value';
    public const KEY_DATA_PARAMETERS = 'parameters';
    public const KEY_SOURCE = 'source';
    public const KEY_METHOD = 'method';
    public const KEY_METHODS = 'methods';
    public const KEY_DESCRIPTION = 'description';
    public const KEY_REAL_SERVICE_METHOD = 'realMethod';
    public const KEY_INPUT_ARRAY_SIZE_LIMIT = 'input-array-size-limit';
    /**#@-*/

    /**
     * @inheritdoc
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
            $soapMethod = $serviceMethod;
            if ($soapOperationNode = $route->attributes->getNamedItem('soapOperation')) {
                $soapMethod = trim($soapOperationNode->nodeValue);
            }
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
            $data = $this->convertMethodParameters($route->getElementsByTagName('parameter'));
            $serviceData = $data;

            if (!isset($serviceClassData[self::KEY_METHODS][$soapMethod])) {
                $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_ACL_RESOURCES] = $resourcePermissionSet;
            } else {
                $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_ACL_RESOURCES] =
                    array_unique(
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        array_merge(
                            $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_ACL_RESOURCES],
                            $resourcePermissionSet
                        )
                    );
                $serviceData = [];
            }

            $method = $route->attributes->getNamedItem('method')->nodeValue;
            $secureNode = $route->attributes->getNamedItem('secure');
            $secure = $secureNode ? (bool)trim($secureNode->nodeValue) : false;

            $arraySizeLimit = $this->getInputArraySizeLimit($route);

            // We could handle merging here by checking if the route already exists
            $result[self::KEY_ROUTES][$url][$method] = [
                self::KEY_SECURE => $secure,
                self::KEY_SERVICE => [
                    self::KEY_SERVICE_CLASS => $serviceClass,
                    self::KEY_SERVICE_METHOD => $serviceMethod,
                ],
                self::KEY_ACL_RESOURCES => $resourceReferences,
                self::KEY_DATA_PARAMETERS => $data,
                self::KEY_INPUT_ARRAY_SIZE_LIMIT => $arraySizeLimit,
            ];

            $serviceSecure = false;
            if (isset($serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_SECURE])) {
                $serviceSecure = $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_SECURE];
            }
            if (!isset($serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_REAL_SERVICE_METHOD])) {
                $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_REAL_SERVICE_METHOD] = $serviceMethod;
            }
            if (!isset($serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_INPUT_ARRAY_SIZE_LIMIT])) {
                $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_INPUT_ARRAY_SIZE_LIMIT] = $arraySizeLimit;
            }
            $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_SECURE] = $serviceSecure || $secure;
            $serviceClassData[self::KEY_METHODS][$soapMethod][self::KEY_DATA_PARAMETERS] = $serviceData;

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
            $force = $forceNode ? filter_var($forceNode->nodeValue, FILTER_VALIDATE_BOOLEAN) : false;
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
     *
     * Assumes the version is the first portion of the URL. For example, '/V1/customers'.
     *
     * @param string $url
     * @return string
     */
    protected function convertVersion($url)
    {
        return substr($url, 1, strpos($url, '/', 1)-1);
    }

    /**
     * Returns array size limit of input data
     *
     * @param \DOMElement $routeDOMElement
     * @return int|null
     */
    private function getInputArraySizeLimit(\DOMElement $routeDOMElement): ?int
    {
        /** @var \DOMElement $dataDOMElement */
        foreach ($routeDOMElement->getElementsByTagName('data') as $dataDOMElement) {
            if ($dataDOMElement->nodeType === XML_ELEMENT_NODE) {
                $inputArraySizeLimitDOMNode = $dataDOMElement->attributes
                    ->getNamedItem(self::KEY_INPUT_ARRAY_SIZE_LIMIT);
                return ($inputArraySizeLimitDOMNode instanceof \DOMNode)
                    ? (int)$inputArraySizeLimitDOMNode->nodeValue
                    : null;
            }
        }

        return null;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Dependency;

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
    const KEY_METHODS = 'methods';
    const KEY_DESCRIPTION = 'description';
    const KEY_REAL_SERVICE_METHOD = 'realMethod';
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
            $url = trim($route->attributes->getNamedItem('url')->nodeValue);

            $method = $route->attributes->getNamedItem('method')->nodeValue;

            // We could handle merging here by checking if the route already exists
            $result[self::KEY_ROUTES][$url][$method] = [
                self::KEY_SERVICE => [
                    self::KEY_SERVICE_CLASS => $serviceClass,
                    self::KEY_SERVICE_METHOD => $serviceMethod,
                ],
            ];
        }
        return $result;
    }
}

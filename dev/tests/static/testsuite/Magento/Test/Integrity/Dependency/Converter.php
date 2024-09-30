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
    private const KEY_URL = 'url';
    private const KEY_CLASS = 'class';
    private const KEY_METHOD = 'method';
    private const KEY_ROUTE = 'route';
    private const KEY_ROUTES = 'routes';
    private const KEY_SERVICE = 'service';
    /**#@-*/

    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNodeList $routes */
        $routes = $source->getElementsByTagName(self::KEY_ROUTE);
        /** @var \DOMElement $route */
        foreach ($routes as $route) {
            if ($route->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            /** @var \DOMElement $service */
            $service = $route->getElementsByTagName(self::KEY_SERVICE)->item(0);
            $serviceClass = $service->attributes->getNamedItem(self::KEY_CLASS)->nodeValue;
            $serviceMethod = $service->attributes->getNamedItem(self::KEY_METHOD)->nodeValue;
            $url = trim($route->attributes->getNamedItem(self::KEY_URL)->nodeValue);

            $method = $route->attributes->getNamedItem(self::KEY_METHOD)->nodeValue;

            // We could handle merging here by checking if the route already exists
            $result[self::KEY_ROUTES][$url][$method] = [
                self::KEY_SERVICE => [
                    self::KEY_CLASS => $serviceClass,
                    self::KEY_SERVICE => $serviceMethod,
                ],
            ];
        }
        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model\RouteCustomizationConfig;

/**
 * Converter of route_customization.xml content into array format.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_ROUTE = 'route';
    const KEY_REQUESTED_PATH = 'requested_path';
    const KEY_ENDPOINT = 'endpoint';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function convert($source)
    {
        $result = [];
        /** @var \DOMNodeList $routes */
        $routes = $source->getElementsByTagName(self::KEY_ROUTE);
        /** @var \DOMElement $route */
        foreach ($routes as $route) {
            if (!$this->canConvertXmlNode($route)) {
                continue;
            }
            $requestedPath = $this->getRouteRequestedPath($route);
            $endpoint = $this->getRouteEndpoint($route);
            $result[ltrim($requestedPath, '/')] = $endpoint;
        }
        return $result;
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

        if ($this->getRouteRequestedPath($node) === null) {
            return false;
        }

        if ($this->getRouteEndpoint($node) === null) {
            return false;
        }

        return true;
    }

    /**
     * @param \DOMElement $route
     * @return null|string
     */
    private function getRouteRequestedPath(\DOMElement $route)
    {
        $requestedPath = $route->attributes->getNamedItem(self::KEY_REQUESTED_PATH)->nodeValue;

        return mb_strlen((string) $requestedPath) === 0 ? null : $requestedPath;
    }

    /**
     * @param \DOMElement $route
     * @return null|string
     */
    private function getRouteEndpoint(\DOMElement $route)
    {
        $endpoint = $route->attributes->getNamedItem(self::KEY_ENDPOINT)->nodeValue;

        return mb_strlen((string) $endpoint) === 0 ? null : $endpoint;
    }
}

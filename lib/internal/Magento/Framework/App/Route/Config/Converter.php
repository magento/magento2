<?php
/**
 * Routes configuration converter
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];

        /** @var \DOMNodeList $routers */
        $routers = $source->getElementsByTagName('router');

        /** @var \DOMNode $router */
        foreach ($routers as $router) {
            $routerConfig = [];
            foreach ($router->attributes as $attribute) {
                $routerConfig[$attribute->nodeName] = $attribute->nodeValue;
            }

            /** @var \DOMNode $routeData */
            foreach ($router->getElementsByTagName('route') as $routeData) {
                $routeConfig = [];
                foreach ($routeData->attributes as $routeAttribute) {
                    $routeConfig[$routeAttribute->nodeName] = $routeAttribute->nodeValue;
                }

                /** @var \DOMNode $module */
                foreach ($routeData->getElementsByTagName('module') as $moduleData) {
                    $moduleConfig = [];
                    foreach ($moduleData->attributes as $moduleAttribute) {
                        $moduleConfig[$moduleAttribute->nodeName] = $moduleAttribute->nodeValue;
                    }
                    $routeConfig['modules'][] = $moduleConfig;
                }
                $routeConfig['modules'] = $this->_sortModulesList($routeConfig['modules']);
                $routerConfig['routes'][$routeData->attributes->getNamedItem('id')->nodeValue] = $routeConfig;
            }

            $output[$router->attributes->getNamedItem('id')->nodeValue] = $routerConfig;
        }

        return $output;
    }

    /**
     * Sort modules list according to before/after attributes
     *
     * @param array $modulesList
     * @return array
     */
    protected function _sortModulesList($modulesList)
    {
        $sortedModulesList = [];

        foreach ($modulesList as $moduleData) {
            if (isset($moduleData['before'])) {
                $position = array_search($moduleData['before'], $sortedModulesList);
                if ($position === false) {
                    $position = 0;
                }
                array_splice($sortedModulesList, $position, 0, $moduleData['name']);
            } elseif (isset($moduleData['after'])) {
                $position = array_search($moduleData['after'], $sortedModulesList);
                if ($position === false) {
                    $position = count($modulesList);
                }
                array_splice($sortedModulesList, $position + 1, 0, $moduleData['name']);
            } else {
                $sortedModulesList[] = $moduleData['name'];
            }
        }

        return $sortedModulesList;
    }
}

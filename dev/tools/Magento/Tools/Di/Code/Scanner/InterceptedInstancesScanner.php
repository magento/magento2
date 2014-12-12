<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\Code\Scanner;

class InterceptedInstancesScanner implements ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $interceptedInstances = [];
        foreach ($files as $fileName) {
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($fileName));
            $xpath = new \DOMXPath($dom);
            /** @var $node \DOMNode */
            foreach ($xpath->query('//type/plugin|//virtualType/plugin') as $node) {
                $parentTypeNode = $node->parentNode->attributes->getNamedItem('name');
                if (is_null($parentTypeNode)) {
                    continue;
                }

                if (!isset($interceptedInstances[$parentTypeNode->nodeValue])) {
                    $interceptedInstances[$parentTypeNode->nodeValue] = [];
                }

                $pluginTypeNode = $node->attributes->getNamedItem('type');
                if (!is_null($pluginTypeNode)) {
                    $interceptedInstances[$parentTypeNode->nodeValue][] = $pluginTypeNode->nodeValue;
                }
            }
        }
        return $interceptedInstances;
    }
}

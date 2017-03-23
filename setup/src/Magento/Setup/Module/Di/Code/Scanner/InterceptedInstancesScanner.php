<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Scanner;

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
                if ($parentTypeNode === null) {
                    continue;
                }

                if (!isset($interceptedInstances[$parentTypeNode->nodeValue])) {
                    $interceptedInstances[$parentTypeNode->nodeValue] = [];
                }

                $pluginTypeNode = $node->attributes->getNamedItem('type');
                if ($pluginTypeNode !== null) {
                    $interceptedInstances[$parentTypeNode->nodeValue][] = $pluginTypeNode->nodeValue;
                }
            }
        }
        return $interceptedInstances;
    }
}

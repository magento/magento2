<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

class PluginScanner implements ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $pluginClassNames = [];
        foreach ($files as $fileName) {
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($fileName));
            $xpath = new \DOMXPath($dom);
            /** @var $node \DOMNode */
            foreach ($xpath->query('//type/plugin|//virtualType/plugin') as $node) {
                $pluginTypeNode = $node->attributes->getNamedItem('type');
                if ($pluginTypeNode !== null) {
                    $pluginClassNames[] = $pluginTypeNode->nodeValue;
                }
            }
        }
        return $pluginClassNames;
    }
}

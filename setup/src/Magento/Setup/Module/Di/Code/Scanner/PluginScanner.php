<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

/**
 * Class \Magento\Setup\Module\Di\Code\Scanner\PluginScanner
 *
 * @since 2.0.0
 */
class PluginScanner implements ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     * @since 2.0.0
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

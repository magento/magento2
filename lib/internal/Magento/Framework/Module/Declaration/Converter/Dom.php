<?php
/**
 * Module declaration xml converter. Converts declaration DOM Document to internal array representation.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Module\Declaration\Converter;

class Dom implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function convert($source)
    {
        $modules = array();
        $xpath = new \DOMXPath($source);
        /** @var $moduleNode \DOMNode */
        foreach ($xpath->query('/config/module') as $moduleNode) {
            $moduleData = array();
            $moduleAttributes = $moduleNode->attributes;
            $nameNode = $moduleAttributes->getNamedItem('name');
            if (is_null($nameNode)) {
                throw new \Exception('Attribute "name" is required for module node.');
            }
            $moduleData['name'] = $nameNode->nodeValue;
            $name = $moduleData['name'];
            $versionNode = $moduleAttributes->getNamedItem('schema_version');
            if (is_null($versionNode)) {
                throw new \Exception("Attribute 'schema_version' is missing for module '{$name}'.");
            }
            $moduleData['schema_version'] = $versionNode->nodeValue;
            $moduleData['sequence'] = [];
            /** @var $childNode \DOMNode */
            foreach ($moduleNode->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case 'sequence':
                        $moduleData['sequence'] = $this->_readModules($childNode);
                        break;
                }
            }
            // Use module name as a key in the result array to allow quick access to module configuration
            $modules[$nameNode->nodeValue] = $moduleData;
        }
        return $modules;
    }

    /**
     * Convert module depends node into assoc array
     *
     * @param \DOMNode $node
     * @return array
     * @throws \Exception
     */
    protected function _readModules(\DOMNode $node)
    {
        $result = [];
        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeName) {
                case 'module':
                    $nameNode = $childNode->attributes->getNamedItem('name');
                    if (is_null($nameNode)) {
                        throw new \Exception('Attribute "name" is required for module node.');
                    }
                    $result[] = $nameNode->nodeValue;
                    break;
            }
        }
        return $result;
    }
}

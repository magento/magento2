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
            $versionNode = $moduleAttributes->getNamedItem('schema_version');
            if (is_null($versionNode)) {
                throw new \Exception('Attribute "schema_version" is required for module node.');
            }
            $moduleData['schema_version'] = $versionNode->nodeValue;
            $activeNode = $moduleAttributes->getNamedItem('active');
            if (is_null($activeNode)) {
                throw new \Exception('Attribute "active" is required for module node.');
            }
            $moduleData['active'] = $activeNode->nodeValue == 'false' ? false : true;
            $moduleData['dependencies'] = array(
                'modules' => array(),
                'extensions' => array('strict' => array(), 'alternatives' => array())
            );
            /** @var $childNode \DOMNode */
            foreach ($moduleNode->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case 'depends':
                        $moduleData['dependencies'] = array_merge(
                            $moduleData['dependencies'],
                            $this->_convertExtensionDependencies($childNode)
                        );
                        break;
                    case 'sequence':
                        $moduleData['dependencies'] = array_merge(
                            $moduleData['dependencies'],
                            $this->_convertModuleDependencies($childNode)
                        );
                        break;
                }
            }
            // Use module name as a key in the result array to allow quick access to module configuration
            $modules[$nameNode->nodeValue] = $moduleData;
        }
        return $modules;
    }

    /**
     * Convert extension depends node into assoc array
     *
     * @param \DOMNode $dependsNode
     * @return array
     * @throws \Exception
     */
    protected function _convertExtensionDependencies(\DOMNode $dependsNode)
    {
        $dependencies = array('extensions' => array('strict' => array(), 'alternatives' => array()));
        /** @var $childNode \DOMNode */
        foreach ($dependsNode->childNodes as $childNode) {
            switch ($childNode->nodeName) {
                case 'extension':
                    $dependencies['extensions']['strict'][] = $this->_convertExtensionNode($childNode);
                    break;
                case 'choice':
                    $alternatives = array();
                    /** @var $extensionNode \DOMNode */
                    foreach ($childNode->childNodes as $extensionNode) {
                        switch ($extensionNode->nodeName) {
                            case 'extension':
                                $alternatives[] = $this->_convertExtensionNode($extensionNode);
                                break;
                        }
                    }
                    if (empty($alternatives)) {
                        throw new \Exception('Node "choice" cannot be empty.');
                    }
                    $dependencies['extensions']['alternatives'][] = $alternatives;
                    break;
            }
        }
        return $dependencies;
    }

    /**
     * Convert extension node into assoc array
     *
     * @param \DOMNode $extensionNode
     * @return array
     * @throws \Exception
     */
    protected function _convertExtensionNode(\DOMNode $extensionNode)
    {
        $extensionData = array();
        $nameNode = $extensionNode->attributes->getNamedItem('name');
        if (is_null($nameNode)) {
            throw new \Exception('Attribute "name" is required for extension node.');
        }
        $extensionData['name'] = $nameNode->nodeValue;
        $minVersionNode = $extensionNode->attributes->getNamedItem('minVersion');
        if (!is_null($minVersionNode)) {
            $extensionData['minVersion'] = $minVersionNode->nodeValue;
        }
        return $extensionData;
    }

    /**
     * Convert module depends node into assoc array
     *
     * @param \DOMNode $dependsNode
     * @return array
     * @throws \Exception
     */
    protected function _convertModuleDependencies(\DOMNode $dependsNode)
    {
        $dependencies = array('modules' => array());
        /** @var $childNode \DOMNode */
        foreach ($dependsNode->childNodes as $childNode) {
            switch ($childNode->nodeName) {
                case 'module':
                    $nameNode = $childNode->attributes->getNamedItem('name');
                    if (is_null($nameNode)) {
                        throw new \Exception('Attribute "name" is required for module node.');
                    }
                    $dependencies['modules'][] = $nameNode->nodeValue;
                    break;
            }
        }
        return $dependencies;
    }
}

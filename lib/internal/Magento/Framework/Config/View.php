<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * View configuration files handler
 */
namespace Magento\Framework\Config;

class View extends \Magento\Framework\Config\AbstractXml
{
    /**
     * Path to view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return __DIR__ . '/etc/view.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _extractData(\DOMDocument $dom)
    {
        $result = [];
        /** @var $varsNode \DOMElement */
        foreach ($dom->childNodes->item(0)/*root*/->childNodes as $childNode) {
            switch ($childNode->tagName) {
                case 'vars':
                    $moduleName = $childNode->getAttribute('module');
                    /** @var $varNode \DOMElement */
                    foreach ($childNode->getElementsByTagName('var') as $varNode) {
                        $varName = $varNode->getAttribute('name');
                        $varValue = $varNode->nodeValue;
                        $result[$childNode->tagName][$moduleName][$varName] = $varValue;
                    }
                    break;
                break;
                case 'media':
                    $moduleName = $childNode->getAttribute('module');
                    /** @var \DOMElement $node */
                    foreach ($childNode->getElementsByTagName('image') as $node) {
                        $imageId = $node->getAttribute('id');
                        $result[$childNode->tagName][$moduleName]['images'][$imageId]['type'] = $node->getAttribute('type');
                        foreach ($node->childNodes as $attribute) {
                            if ($attribute->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $nodeValue = $attribute->nodeValue;
                            $result[$childNode->tagName][$moduleName]['images'][$imageId][$attribute->tagName] = $nodeValue;
                        }
                    }
                    foreach ($childNode->getElementsByTagName('video') as $node) {
                        $imageId = $node->getAttribute('id');
                        $result[$childNode->tagName][$moduleName]['videos'][$imageId]['type'] = $node->getAttribute('type');
                        foreach ($node->childNodes as $attribute) {
                            if ($attribute->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $nodeValue = $attribute->nodeValue;
                            $result[$childNode->tagName][$moduleName]['videos'][$imageId][$attribute->tagName] = $nodeValue;
                        }
                    }
                    break;
                case 'exclude':
                    /** @var $itemNode \DOMElement */
                    foreach ($childNode->getElementsByTagName('item') as $itemNode) {
                        $itemType = $itemNode->getAttribute('type');
                        $result[$childNode->tagName][$itemType][] = $itemNode->nodeValue;
                    }
                    break;
            }
        }
        return $result;
    }

    /**
     * Get a list of variables in scope of specified module
     *
     * Returns array(<var_name> => <var_value>)
     *
     * @param string $module
     * @return array
     */
    public function getVars($module)
    {
        return isset($this->_data['vars'][$module]) ? $this->_data['vars'][$module] : [];
    }

    /**
     * Get value of a configuration option variable
     *
     * @param string $module
     * @param string $var
     * @return string|false
     */
    public function getVarValue($module, $var)
    {
        return isset($this->_data['vars'][$module][$var]) ? $this->_data['vars'][$module][$var] : false;
    }

    /**
     * Retrieve a list videos attributes in scope of specified module
     *
     * @param $module
     * @param $var
     * @return bool
     */
    public function getVideoAttributeValue($module, $var)
    {
        return isset($this->_data['media'][$module]['videos'][$var][$var]) ? $this->_data['media'][$module]['videos'][$var][$var] : false;
    }

    /**
     * Retrieve a list images attributes in scope of specified module
     *
     * @param string $module
     * @return array
     */
    public function getImages($module)
    {
        return isset($this->_data['media'][$module]['images']) ? $this->_data['media'][$module]['images'] : [];
    }

    /**
     * Retrieve a list media attributes in scope of specified module
     *
     * @param string $module
     * @return array
     */
    public function getMedia($module)
    {
        return isset($this->_data['media'][$module]) ? $this->_data['media'][$module] : [];
    }

    /**
     * Retrieve array of image attributes
     *
     * @param string $module
     * @param string $imageId
     * @return array
     */
    public function getImageAttributes($module, $imageId)
    {
        return isset($this->_data['media'][$module]['images'][$imageId])
            ? $this->_data['media'][$module]['images'][$imageId]
            : [];
    }

    /**
     * Return copy of DOM
     *
     * @return \Magento\Framework\Config\Dom
     */
    public function getDomConfigCopy()
    {
        return clone $this->_getDomConfigModel();
    }

    /**
     * Getter for initial view.xml contents
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' .
               '<view xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></view>';
    }

    /**
     * Variables are identified by module and name
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return [
            '/view/vars' => 'module',
            '/view/vars/var' => 'name',
            '/view/exclude/item' => ['type', 'item'],
            '/view/media' => 'module',
            '/view/media/image' => ['id', 'type'],
            '/view/media/video' => ['id', 'type'],
        ];
    }

    /**
     * Get excluded file list
     *
     * @return array
     */
    public function getExcludedFiles()
    {
        $items = $this->getItems();
        return isset($items['file']) ? $items['file'] : [];
    }

    /**
     * Get excluded directory list
     *
     * @return array
     */
    public function getExcludedDir()
    {
        $items = $this->getItems();
        return isset($items['directory']) ? $items['directory'] : [];
    }

    /**
     * Get a list of excludes
     *
     * @return array
     */
    protected function getItems()
    {
        return isset($this->_data['exclude']) ? $this->_data['exclude'] : [];
    }
}

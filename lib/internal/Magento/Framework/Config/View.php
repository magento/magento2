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
                    $result[$childNode->tagName][$moduleName] = $this->parseVarElement($childNode);
                    break;
                case 'images':
                    $moduleName = $childNode->getAttribute('module');
                    /** @var \DOMElement $node */
                    foreach ($childNode->getElementsByTagName('image') as $node) {
                        $imageId = $node->getAttribute('id');
                        $result[$childNode->tagName][$moduleName][$imageId]['type'] = $node->getAttribute('type');
                        foreach ($node->childNodes as $attribute) {
                            if ($attribute->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $nodeValue = $attribute->nodeValue;
                            $result[$childNode->tagName][$moduleName][$imageId][$attribute->tagName] = $nodeValue;
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
     * @param \DOMElement $node
     * @return string|[]
     */
    protected function parseVarElement(\DOMElement $node)
    {
        if ($node->getElementsByTagName('var')->length) {
            $result = [];
            foreach ($node->getElementsByTagName('var') as $varNode) {
                $varName = $varNode->getAttribute('name');
                $result[$varName] = $this->parseVarElement($varNode);
            }
        } else {
            $result = $node->nodeValue;
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
     * @return string|false|array
     */
    public function getVarValue($module, $var)
    {
        $value = $this->_data['vars'][$module];
        foreach (explode('/', $var) as $node) {
            if (is_array($value) && isset($value[$node])) {
                $value = $value[$node];
            } else {
                return false;
            }
        }
        return $value;
    }

    /**
     * Retrieve a list images attributes in scope of specified module
     *
     * @param string $module
     * @return array
     */
    public function getImages($module)
    {
        return isset($this->_data['images'][$module]) ? $this->_data['images'][$module] : [];
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
        return isset($this->_data['images'][$module][$imageId])
            ? $this->_data['images'][$module][$imageId]
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
            '/view/images' => 'modulle',
            '/view/images/image' => ['id', 'type'],
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

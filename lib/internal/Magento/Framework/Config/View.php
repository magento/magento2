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
                case 'exclude':
                    /** @var $areaNode \DOMElement */
                    foreach ($childNode->getElementsByTagName('area') as $areaNode) {
                        $areaName = $areaNode->getAttribute('name');
                        foreach ($areaNode->getElementsByTagName('item') as $itemNode) {
                            $itemType = $itemNode->getAttribute('type');
                            $result[$childNode->tagName][$areaName][$itemType][] = $itemNode->nodeValue;
                        }
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
        return ['/view/vars' => 'module', '/view/vars/var' => 'name', '/view/exclude/area' => 'name'];
    }

    /**
     * Get excluded file list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedFiles($area)
    {
        $items = $this->getItems($area);
        return isset($items['file']) ? $items['file'] : [];
    }

    /**
     * Get excluded directory list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedDir($area)
    {
        $items = $this->getItems($area);
        return isset($items['directory']) ? $items['directory'] : [];
    }

    /**
     * Get a list of excludes in scope of specified area
     *
     * @param string $area
     * @return array
     */
    protected function getItems($area)
    {
        return isset($this->_data['exclude'][$area]) ? $this->_data['exclude'][$area] : [];
    }

}

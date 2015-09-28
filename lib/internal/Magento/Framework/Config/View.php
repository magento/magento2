<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * View configuration files handler
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Reader\Xsd\Reader;
use Magento\Framework\Config\Reader\Xsd\Media\TypeDataExtractorPool;

class View extends \Magento\Framework\Config\AbstractXml
{
    /*
     * @var \Magento\Framework\Config\Reader\Xsd\Reader
     */
    protected $xsdReader;

    /*
     * @var \Magento\Framework\Config\Reader\Xsd\Media\TypeDataExtractorPool
     */
    protected $extractorPool;

    /*
     * @var array
     */
    protected $xpath;

    /**
     * @param array $configFiles
     * @param array $xpath
     * @param Reader $xsdReader
     * @param TypeDataExtractorPool $extractorPool
     */
    public function __construct(
        $configFiles,
        $xpath = [],
        Reader $xsdReader,
        TypeDataExtractorPool $extractorPool
    ) {
        $this->xsdReader = $xsdReader;
        $this->xpath = $xpath;
        $this->extractorPool = $extractorPool;
        parent::__construct($configFiles);
    }

    /**
     * Merged file view.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        $configXsd = $this->xsdReader->read();
        return $configXsd;
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
        foreach ($dom->childNodes->item(0)->childNodes as $childNode) {
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
                    /** @var $itemNode \DOMElement */
                    foreach ($childNode->getElementsByTagName('item') as $itemNode) {
                        $itemType = $itemNode->getAttribute('type');
                        $result[$childNode->tagName][$itemType][] = $itemNode->nodeValue;
                    }
                    break;
                default:
                    $imagesNodesArray = $this->extractorPool->nodeProcessor($childNode->tagName)->process($childNode);
                    $result = array_merge($result, $imagesNodesArray);
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
     * Retrieve a list media attributes in scope of specified module
     *
     * @param string $module
     * @param string $mediaType
     * @return array
     */
    public function getMediaEntities($module, $mediaType)
    {
        return isset($this->_data[$mediaType][$module]) ? $this->_data[$mediaType][$module] : [];
    }

    /**
     * Retrieve array of media attributes
     *
     * @param $module
     * @param $mediaType
     * @param $mediaId
     * @return array
     */
    public function getMediaAttributes($module, $mediaType, $mediaId)
    {
        return isset($this->_data[$mediaType][$module][$mediaId])
            ? $this->_data[$mediaType][$module][$mediaId]
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
        $idAttributes = $this->addIdAttributes($this->xpath);
        return $idAttributes;
    }

    /**
     * Add attributes for module identification
     *
     * @param $xpath
     * @return array
     */
    protected function addIdAttributes($xpath)
    {
        $idAttributes = [
            '/view/vars' => 'module',
            '/view/vars/var' => 'name',
            '/view/exclude/item' => ['type', 'item'],
        ];
        if (is_array($xpath)) {
            foreach ($xpath as $attribute) {
                if (is_array($attribute)) {
                    foreach ($attribute as $newAttribute) {
                        if (isset($newAttribute['path']) && isset($newAttribute['id'])) {
                            $idAttributes[$newAttribute['path']] = $newAttribute['id'];
                        }
                    }
                }
            }
        }
        return $idAttributes;
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

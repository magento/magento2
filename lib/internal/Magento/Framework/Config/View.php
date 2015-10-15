<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * View configuration files handler
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\View\Xsd\Reader;
use Magento\Framework\View\Xsd\Media\TypeDataExtractorPool;

class View extends \Magento\Framework\Config\AbstractXml
{
    /** @var UrnResolver */
    protected $urnResolver;

    /**
     * @var \Magento\Framework\View\Xsd\Media\TypeDataExtractorPool
     */
    protected $extractorPool;

    /**
     * @var array
     */
    protected $xpath;

    /**
     * @var Reader
     */
    private $xsdReader;

    /**
     * @param array $configFiles
     * @param Reader $xsdReader
     * @param UrnResolver $urnResolver
     * @param TypeDataExtractorPool $extractorPool
     * @param array $xpath
     */
    public function __construct(
        $configFiles,
        Reader $xsdReader,
        UrnResolver $urnResolver,
        TypeDataExtractorPool $extractorPool,
        $xpath = []
    ) {
        $this->xpath = $xpath;
        $this->extractorPool = $extractorPool;
        $this->urnResolver = $urnResolver;
        $this->xsdReader = $xsdReader;
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
                    $result[$childNode->tagName][$moduleName] = $this->parseVarElement($childNode);
                    break;
                case 'exclude':
                    /** @var $itemNode \DOMElement */
                    foreach ($childNode->getElementsByTagName('item') as $itemNode) {
                        $itemType = $itemNode->getAttribute('type');
                        $result[$childNode->tagName][$itemType][] = $itemNode->nodeValue;
                    }
                    break;
                case 'media':
                    foreach ($childNode->childNodes as $mediaNode) {
                        if ($mediaNode instanceof \DOMElement) {
                            $mediaNodesArray =
                                $this->extractorPool->nodeProcessor($mediaNode->tagName)->process(
                                    $mediaNode,
                                    $childNode->tagName
                                );
                            $result = array_merge($result, $mediaNodesArray);
                        }
                    }
                    break;
            }
        }
        return $result;
    }

    /**
     * Recursive parser for <var> nodes
     *
     * @param \DOMElement $node
     * @return string|boolean|number|null|[]
     */
    protected function parseVarElement(\DOMElement $node)
    {
        $result = [];
        for ($varNode = $node->firstChild; $varNode !== null; $varNode = $varNode->nextSibling) {
            if ($varNode instanceof \DOMElement && $varNode->tagName == "var") {
                $varName = $varNode->getAttribute('name');
                $result[$varName] = $this->parseVarElement($varNode);
            }
        }
        if (!count($result)) {
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
        if (!isset($this->_data['vars'][$module])) {
            return false;
        }

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
     * Retrieve a list media attributes in scope of specified module
     *
     * @param string $module
     * @param string $mediaType
     * @return array
     */
    public function getMediaEntities($module, $mediaType)
    {
        return isset($this->_data['media'][$module][$mediaType]) ? $this->_data['media'][$module][$mediaType] : [];
    }

    /**
     * Retrieve array of media attributes
     *
     * @param string $module
     * @param string $mediaType
     * @param string $mediaId
     * @return array
     */
    public function getMediaAttributes($module, $mediaType, $mediaId)
    {
        return isset($this->_data['media'][$module][$mediaType][$mediaId])
            ? $this->_data['media'][$module][$mediaType][$mediaId]
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
     * @param array $xpath
     * @return array
     */
    protected function addIdAttributes($xpath)
    {
        $idAttributes = [
            '/view/vars' => 'module',
            '/view/vars/var' => 'name',
            '/view/exclude/item' => ['type', 'item'],
        ];
        foreach ($xpath as $attribute) {
            if (is_array($attribute)) {
                foreach ($attribute as $key => $id) {
                    if (count($id) > 1) {
                        $idAttributes[$key] = array_values($id);
                    } else {
                        $idAttributes[$key] = array_shift($id);
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

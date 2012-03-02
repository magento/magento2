<?php
/**
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
 * @category    Magento
 * @package     Framework
 * @subpackage  Config
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento configuration XML DOM utility
 */
class Magento_Config_Dom
{
    /**
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * @var array
     */
    protected $_idAttributes;

    /**
     * Build DOM with initial XML contents and specifying identifier attributes for merging
     *
     * Format of $idAttributes: array('/xpath/to/some/node' => 'id_attribute_name')
     * The path to ID attribute name should not include any attribute notations or modifiers -- only node names
     *
     * @param string $xml
     * @param array $idAttributes
     */
    public function __construct($xml, array $idAttributes = array())
    {
        $this->_dom          = $this->_initDom($xml);
        $this->_idAttributes = $idAttributes;
    }

    /**
     * Merge $xml into DOM document
     *
     * @param string $xml
     * @return void
     */
    public function merge($xml)
    {
        $dom = $this->_initDom($xml);
        $this->_mergeNode($dom->documentElement, '');
    }

    /**
     * Recursive merging of the DOMElement into the original document
     *
     * Algorithm:
     * 1. Find the same node in original document
     * 2. Extend and override original document node attributes and scalar value if found
     * 3. Append new node if original document doesn't have the same node
     *
     * @param DOMElement $node
     * @param string $parentPath path to parent node
     */
    protected function _mergeNode(DOMElement $node, $parentPath)
    {
        /* Identify node path based on parent path and node attributes */
        $path = $parentPath . '/' . $node->tagName;
        $idAttribute = $this->_findIdAttribute($path);
        if ($idAttribute && $id = $node->getAttribute($idAttribute)) {
            $path .= "[@{$idAttribute}='{$id}']";
        }

        $matchedNode = $this->_getMatchedNode($path);

        /* Update matched node attributes and value */
        if ($matchedNode) {
            foreach ($node->attributes as $attribute) {
                if ($matchedNode->getAttribute($attribute->name)) {
                    $matchedNode->setAttribute($attribute->name, $attribute->value);
                } else {
                    $matchedNode->setAttributeNode($attribute);
                }
            }
            /* Merge child nodes */
            if ($node->hasChildNodes()) {
                /* override node value */
                if ($node->childNodes->length == 1 && $node->childNodes->item(0) instanceof DOMText) {
                    $matchedNode->nodeValue = $node->childNodes->item(0)->nodeValue;
                } else { /* recursive merge for all child nodes */
                    foreach ($node->childNodes as $childNode) {
                        if ($childNode instanceof DOMElement) {
                            $this->_mergeNode($childNode, $path);
                        }
                    }
                }
            }
        } else {
            /* Add node as is to the document under the same parent element */
            $parentMatchedNode = $this->_getMatchedNode($parentPath);
            $newNode = $this->_dom->importNode($node, true);
            $parentMatchedNode->appendChild($newNode);
        }
    }

    /**
     * Determine whether an XPath matches registered identifiable attribute
     *
     * @param string $xPath
     * @return string|false
     */
    protected function _findIdAttribute($xPath)
    {
        $path = preg_replace('/\[@[^\]]+?\]/', '', $xPath);
        return isset($this->_idAttributes[$path]) ? $this->_idAttributes[$path] : false;
    }

    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @throws Magento_Exception an exception is possible if original document contains multiple nodes for identifier
     * @return DOMElement | null
     */
    protected function _getMatchedNode($nodePath)
    {
        $xPath  = new DOMXPath($this->_dom);
        $matchedNodes = $xPath->query($nodePath);
        $node = null;
        if ($matchedNodes->length > 1) {
            throw new Magento_Exception("More than one node matching the query: {$nodePath}");
        } elseif ($matchedNodes->length == 1) {
            $node = $matchedNodes->item(0);
        }
        return $node;
    }

    /**
     * DOM document getter
     *
     * @return DOMDocument
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * Create DOM document based on $xml parameter
     *
     * @param string $xml
     * @return DOMDocument
     */
    protected function _initDom($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom;
    }

    /**
     * Validate self contents towards to specified schema
     *
     * @param string $schemaFile absolute path to schema file
     * @param array &$errors
     * @return bool
     */
    public function validate($schemaFile, &$errors = array())
    {
        libxml_use_internal_errors(true);
        $result = $this->_dom->schemaValidate($schemaFile);
        $errors = libxml_get_errors();
        libxml_use_internal_errors(false);
        return $result;
    }
}
